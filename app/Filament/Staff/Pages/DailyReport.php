<?php

namespace App\Filament\Staff\Pages;

use App\Models\Attachment;
use App\Models\DailyReport as DailyReportModel;
use App\Models\Project;
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
    protected static ?string $navigationGroup = 'Project';
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
                Forms\Components\TextInput::make('workers_count')
                    ->label('Number of Workers')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Forms\Components\FileUpload::make('attachments')
                    ->label('Attachments')
                    ->multiple()
                    ->enableReordering()
                    ->disk('local')
                    ->directory('daily-reports')
                    ->visibility('private')
                    ->openable()
                    ->downloadable()
                    ->acceptedFileTypes(['image/*', 'video/*', 'application/pdf'])
                    ->maxSize(10240)
                    ->helperText('You can upload multiple images, videos, or PDFs. Max size: 10MB per file.')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('progress_percentage')
                    ->label('Progress Added (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                Forms\Components\Textarea::make('work_description')
                    ->label('Today\'s Work Description')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('issues')
                    ->label('Issues (Optional)')
                    ->rows(2)
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
                Tables\Columns\TextColumn::make('workers_count')->label('Workers Count'),
                Tables\Columns\TextColumn::make('progress_percentage')->label('Progress (%)'),
                Tables\Columns\ImageColumn::make('attachment_images')
                    ->label('Images')
                    ->getStateUsing(fn(DailyReportModel $record): array => $record->attachments
                        ->filter(fn(Attachment $attachment): bool => str_starts_with($attachment->file_type ?? '', 'image/'))
                        ->map(fn(Attachment $attachment): string => Storage::disk('local')->temporaryUrl($attachment->file_path, now()->addMinutes(5)))
                        ->values()
                        ->all())
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->size(48),
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
