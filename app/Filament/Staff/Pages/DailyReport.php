<?php

namespace App\Filament\Staff\Pages;

use App\Models\DailyReport as DailyReportModel;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DailyReport extends Page implements HasForms
{
    use InteractsWithForms;

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
                Forms\Components\Section::make('Daily Activity Report')
                    ->description('Report on daily field activities')
                    ->schema([
                        Forms\Components\Select::make('project_id')
                            ->label('Select Project')
                            ->options(fn () => Project::whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
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
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $data['user_id'] = auth()->id();

        DailyReportModel::create($data);

        Notification::make()
            ->title('Daily Report Submitted Successfully')
            ->success()
            ->send();

        $this->form->fill();
    }

    public function getReportsHistory()
    {
        return DailyReportModel::where('user_id', auth()->id())
            ->with('project')
            ->orderBy('report_date', 'desc')
            ->limit(10)
            ->get();
    }
}