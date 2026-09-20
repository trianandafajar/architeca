<?php

namespace App\Filament\Staff\Pages;

use App\Models\Attachment;
use App\Models\DailyReport as DailyReportModel;
use App\Models\ProjectTask;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Filament\Infolists;
use Filament\Support\Enums\MaxWidth;

class DailyReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.staff.pages.daily-report';
    protected static ?string $title = 'Daily Report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->label('Select Project')
                    ->options(fn() => Project::whereHas('members', fn($q) => $q->where('user_id', auth()->id()))
                        ->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('report_date')
                    ->label('Report Date')
                    ->default(now())
                    ->required(),
                Forms\Components\CheckboxList::make('completed_task_ids')
                    ->label('Tugas')
                    ->options(function (array $state) {
                        $projectId = $state['project_id'] ?? null;
                        if (! $projectId) {
                            return [];
                        }
                        return \App\Models\ProjectTask::where('project_id', $projectId)
                            ->where('assigned_to', auth()->id())
                            ->where('is_completed', false)
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('work_description')
                    ->label('Catatan')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('attachments')
                    ->label('Foto Bukti')
                    ->multiple()
                    ->enableReordering()
                    ->disk('local')
                    ->directory('daily-reports')
                    ->visibility('private')
                    ->openable()
                    ->downloadable()
                    ->acceptedFileTypes(['image/*'])
                    ->maxSize(5120)
                    ->helperText('Upload foto bukti (maks 5 MB per gambar).')
                    ->columnSpanFull(),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('submit')
                        ->label('Submit Report')
                        ->button()
                        ->color('primary')
                        ->icon('heroicon-o-check')
                        ->action('submit')
                        ->requiresConfirmation()
                        ->modalHeading('Submit Daily Report')
                        ->modalSubheading('Are you sure you want to submit this daily report? Once submitted, it cannot be edited.')
                        ->modalButton('Yes, Submit'),
                ])
            ])->columns(2)
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $attachmentPaths = array_values($data['attachments'] ?? []);
        unset($data['attachments']);

        // Update task completion status
        if (! empty($data['completed_task_ids'])) {
            \App\Models\ProjectTask::whereIn('id', $data['completed_task_ids'])
                ->update(['is_completed' => true]);
        }
        unset($data['completed_task_ids']);

        $data['user_id'] = auth()->id();


        $report = DailyReportModel::create($data);

        foreach ($attachmentPaths as $path) {
            Attachment::create([
                'project_id' => $report->project_id,
                'user_id' => auth()->id(),
                'attachable_type' => $report->getMorphClass(),
                'attachable_id' => $report->getKey(),
                'file_path' => $path,
                'file_type' => Storage::disk('local')->mimeType($path),
            ]);
        }

        Notification::make()
            ->title('Daily Report Submitted Successfully')
            ->success()
            ->send();

        $this->form->fill();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DailyReportModel::query()
                    ->where('user_id', auth()->id())
                    ->with(['project', 'attachments'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('project.name')->label('Project'),
                Tables\Columns\TextColumn::make('report_date')->date()->label('Report Date'),
                Tables\Columns\ImageColumn::make('attachment_images')
                    ->label('Photos')
                    ->getStateUsing(fn(DailyReportModel $record): array => $record->attachments
                        ->filter(fn(Attachment $attachment): bool => str_starts_with($attachment->file_type ?? '', 'image/'))
                        ->map(fn(Attachment $attachment): string => Storage::disk('local')->temporaryUrl($attachment->file_path, now()->addMinutes(5)))
                        ->values()
                        ->all())
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->size(48),
                Tables\Columns\TextColumn::make('work_description')->label('Notes')->limit(50),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name'),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Daily Report Details')
                    ->modalSubheading('Details of the daily report submitted on :date', ['date' => fn(DailyReportModel $record): string => $record->report_date->format('d M Y')])
                    ->modalWidth(MaxWidth::FiveExtraLarge)
                    ->infolist([
                        Infolists\Components\Section::make('Report Information')
                            ->schema([
                                Infolists\Components\Grid::make(4)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('project.name')
                                            ->label('Project')
                                            ->icon('heroicon-o-briefcase'),

                                        Infolists\Components\TextEntry::make('report_date')
                                            ->label('Report Date')
                                            ->date('d M Y')
                                            ->icon('heroicon-o-calendar'),

                                        Infolists\Components\TextEntry::make('workers_count')
                                            ->label('Workers')
                                            ->icon('heroicon-o-users'),

                                        Infolists\Components\TextEntry::make('progress_percentage')
                                            ->label('Progress')
                                            ->suffix('%')
                                            ->badge(),
                                    ]),

                                Infolists\Components\TextEntry::make('work_description')
                                    ->label('Work Description')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('issues')
                                    ->label('Issues')
                                    ->placeholder('No issues reported.')
                                    ->columnSpanFull(),
                            ]),

                        Infolists\Components\Section::make('Attachments')
                            ->icon('heroicon-o-paper-clip')
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('attachments')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\ImageEntry::make('file_path')
                                            ->label('')
                                            ->disk('local')
                                            ->visibility('private')
                                            ->height(160)
                                            ->extraImgAttributes([
                                                'class' => 'rounded-lg object-cover w-full',
                                            ]),

                                        Infolists\Components\TextEntry::make('caption')
                                            ->label('Caption')
                                            ->placeholder('No caption')
                                            ->limit(40),

                                        Infolists\Components\TextEntry::make('file_type')
                                            ->label('Type')
                                            ->badge(),

                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Uploaded')
                                            ->dateTime('d M Y, H:i'),
                                    ])
                                    ->grid(3)
                                    ->contained(false),
                            ]),
                    ]),
            ])
            ->paginated([10, 25, 50])
            ->bulkActions([]);
    }



    public function getSubheading(): ?string
    {
        return 'Fill in the daily work activity report from the field.';
    }
}
