<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Attachment;
use App\Models\DailyReport;
use App\Models\Expense;
use App\Models\ProgressUpdate;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AttachmentsRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Attachments';

    protected static ?string $icon = 'heroicon-o-paper-clip';

    protected static ?string $recordTitleAttribute = 'file_path';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('attachable_type')
                    ->label('Attach to')
                    ->options([
                        (new Project)->getMorphClass() => 'Project',
                        (new DailyReport)->getMorphClass() => 'Daily Report',
                        (new ProgressUpdate)->getMorphClass() => 'Progress Update',
                        (new Expense)->getMorphClass() => 'Expense',
                    ])
                    ->default((new Project)->getMorphClass())
                    ->prefixIcon('heroicon-m-link')
                    ->native(false)
                    ->required()
                    ->live()
                    ->afterStateUpdated(
                        fn (Set $set, ?string $state) => $set(
                            'attachable_id',
                            $state === (new Project)->getMorphClass()
                                ? $this->getOwnerRecord()->getKey()
                                : null,
                        )
                    ),

                Forms\Components\Select::make('attachable_id')
                    ->label('Target record')
                    ->placeholder('Select target record')
                    ->options(
                        fn (Get $get): array =>
                            $this->getTargetOptions($get('attachable_type'))
                    )
                    ->default(
                        fn () => $this->getOwnerRecord()->getKey()
                    )
                    ->prefixIcon('heroicon-m-square-3-stack-3d')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->in(
                        fn (Get $get): array =>
                            array_keys(
                                $this->getTargetOptions(
                                    $get('attachable_type')
                                )
                            )
                    ),
                    Forms\Components\FileUpload::make('file_path')
                        ->label('Attachment')
                        ->disk(
                            fn (?Attachment $record): string =>
                                $this->getAttachmentDisk($record)
                        )
                        ->visibility(
                            fn (?Attachment $record): string =>
                                $this->getAttachmentDisk($record) === 'local'
                                    ? 'private'
                                    : 'public'
                        )
                        ->directory('attachments')
                        ->openable()
                        ->downloadable()
                        ->previewable()
                        ->helperText('Upload the file you want to attach.')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('caption')
                        ->label('Caption')
                        ->placeholder('Optional description...')
                        ->maxLength(255)
                        ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('attachable'))
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('Preview')
                    ->getStateUsing(fn (Attachment $record): ?string => str_starts_with($record->file_type ?? '', 'image/')
                        || in_array(strtolower(pathinfo($record->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp'], true)
                            ? $record->file_path : null)
                    ->disk(fn (Attachment $record): string => $this->getAttachmentDisk($record))
                    ->visibility(fn (Attachment $record): string => $this->getAttachmentDisk($record) === 'local' ? 'private' : 'public')
                    ->url(fn (Attachment $record): string => $this->getAttachmentUrl($record))
                    ->openUrlInNewTab()
                    ->size(50)
                    ->rounded(),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->formatStateUsing(fn (string $state): string => basename($state))
                    ->url(fn (Attachment $record): string => $this->getAttachmentUrl($record))
                    ->openUrlInNewTab()
                    ->limit(50),
                Tables\Columns\TextColumn::make('source')
                    ->label('From')
                    ->getStateUsing(fn (Attachment $record): string => $record->attachable
                        ? $this->getTargetLabel($record->attachable)
                        : 'Target unavailable')
                    ->wrap(),
                Tables\Columns\TextColumn::make('file_type')
                    ->label('Type'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created by')
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary')
                        ->mutateFormDataUsing(fn (array $data): array => $this->prepareAttachmentData($data)),
                    'Upload file and add caption for this project.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make()
                        ->mutateFormDataUsing(fn (array $data, Attachment $record): array => $this->prepareAttachmentData($data, $record)),
                    'Update file or attachment caption.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Deleted attachments cannot be recovered.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Selected attachments will be deleted and cannot be recovered.',
                    ),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareAttachmentData(array $data, ?Attachment $record = null): array
    {
        $data['file_type'] = Storage::disk($this->getAttachmentDisk($record))->mimeType($data['file_path']) ?: null;

        return $data;
    }

    /** @return array<int|string, string> */
    protected function getTargetOptions(?string $type): array
    {
        $project = $this->getOwnerRecord();
        $records = match ($type) {
            (new Project)->getMorphClass() => collect([$project]),
            (new DailyReport)->getMorphClass() => $project->dailyReports()->latest('report_date')->get(),
            (new ProgressUpdate)->getMorphClass() => $project->progressUpdates()->latest('progress_date')->get(),
            (new Expense)->getMorphClass() => $project->expenses()->latest('expense_date')->get(),
            default => collect(),
        };

        return $records->mapWithKeys(fn (Model $record): array => [$record->getKey() => $this->getTargetLabel($record)])->all();
    }

    protected function getTargetLabel(Model $record): string
    {
        return match (true) {
            $record instanceof Project => 'Project (self) — '.$record->name,
            $record instanceof DailyReport => 'Daily Report #'.$record->getKey().' — '.$record->report_date?->format('d M Y'),
            $record instanceof ProgressUpdate => 'Progress Update #'.$record->getKey().' — '.$record->progress_date?->format('d M Y').' ('.$record->percentage.'%)',
            $record instanceof Expense => 'Expense #'.$record->getKey().' — '.$record->expense_date?->format('d M Y').' / '.$record->category,
            default => class_basename($record).' #'.$record->getKey(),
        };
    }

    protected function getAttachmentDisk(?Attachment $record): string
    {
        if ($record && ! Storage::disk('local')->exists($record->file_path) && Storage::disk('public')->exists($record->file_path)) {
            return 'public';
        }

        return 'local';
    }

    protected function getAttachmentUrl(Attachment $record): string
    {
        $disk = $this->getAttachmentDisk($record);

        return $disk === 'local'
            ? Storage::disk($disk)->temporaryUrl($record->file_path, now()->addMinutes(5))
            : Storage::disk($disk)->url($record->file_path);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
