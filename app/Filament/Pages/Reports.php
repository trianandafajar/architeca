<?php

namespace App\Filament\Pages;

use App\Models\DailyReport;
use App\Models\Expense;
use App\Models\ProgressUpdate;
use App\Models\Project;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string $view = 'filament.admin.pages.reports';

    protected static ?string $navigationGroup = 'Project';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Project Reports';

    public function getViewData(): array
    {
        $projects = Project::with(['budgetItems', 'expenses', 'progressUpdates', 'dailyReports.user'])->get();

        return [
            'totalProjects' => $projects->count(),
            'totalBudget' => $projects->sum('contract_value'),
            'totalExpenses' => Expense::sum('amount'),
            'avgProgress' => round(ProgressUpdate::latest('progress_date')->pluck('percentage')->first() ?? 0),
            'budgetVsExpense' => $projects->map(fn($p) => [
                'name' => $p->name,
                'budget' => $p->contract_value,
                'expense' => $p->expenses->sum('amount'),
                'remaining' => $p->contract_value - $p->expenses->sum('amount'),
            ]),
            'progressReport' => $projects->map(fn($p) => [
                'project' => $p->name,
                'progress' => $p->progressUpdates->sortByDesc('progress_date')->first()?->percentage ?? 0,
                'date' => $p->progressUpdates->sortByDesc('progress_date')->first()?->progress_date->format('d M Y') ?? '-',
                'by' => $p->progressUpdates->sortByDesc('progress_date')->first()?->user?->name ?? '-',
            ]),
            'dailyActivity' => DailyReport::with(['project', 'user'])->latest('report_date')->limit(10)->get()->map(fn($d) => [
                'date' => $d->report_date->format('d M Y'),
                'project' => $d->project->name,
                'staff' => $d->user->name,
                'desc' => $d->work_description,
                'progress' => $d->progress_percentage,
            ]),
        ];
    }
}
