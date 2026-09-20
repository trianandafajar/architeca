<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;
use App\Models\DailyReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class DailyReportsRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'dailyReports';

    protected static ?string $title = 'Daily Reports';

    protected static ?string $icon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'report_date';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
                Forms\Components\DatePicker::make('report_date')
                    ->label('Report Date')
                    ->native(false)
                    ->maxDate(now())
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('workers_count')
                    ->label('Number of Workers')
                    ->numeric()
                    ->default(0),
                Forms\Components\ViewField::make('progress_percentage')
                    ->label('Progress (%)')
                    ->view('filament.forms.components.range-slider')
                    ->default(0)
                    ->rules(['required','numeric','min:0','max:100'])
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('issues')
                    ->label('Issues / Obstacles')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('attachments'))
            ->columns([
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('attachments')
                    ->label('Photos')
                    ->getStateUsing(function ($record) {
                        return $record->attachments
                            ->filter(fn ($attachment) => str_starts_with($attachment->file_type ?? '', 'image/'))
                            ->map(fn ($attachment) => Storage::disk('local')->temporaryUrl(
                                $attachment->file_path,
                                now()->addMinutes(5)
                            ))
                            ->values()
                            ->all();
                    })
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->size(48),
                Tables\Columns\TextColumn::make('work_description')
                    ->label('Notes')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('By'),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Add a daily report to record project activity.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\ViewAction::make(),
                    'View project activity and status on this date.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Update daily report and recorded issues.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Selected daily reports will be deleted and cannot be recovered.',
                    ),
                ]),
            ]);

    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
