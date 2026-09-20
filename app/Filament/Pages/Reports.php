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
    protected static string $view = 'filament.admin.pages.reports';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationParentItem(): ?string
    {
        return 'Projects';
    }

    protected static ?string $title = 'Project Reports';

    public function getSubheading(): ?string
    {
        return 'View and analyze project reports, including budgets, expenses, and progress.';
    }

    public ?int $projectId = null;

    public function mount(): void
    {
        $this->projectId = request()->query('record');
    }

    public function getViewData(): array
    {
        $query = Project::with(['budgetItems', 'expenses', 'progressUpdates', 'dailyReports.user']);
        
        if ($this->projectId) {
            $query->where('id', $this->projectId);
        }

        $projects = $query->get();

        return [
            // ... (keep logic, just ensure $projects is used)
            'totalProjects' => $projects->count(),
            'totalBudget' => $projects->sum('contract_value'),
            'totalExpenses' => $projects->sum(fn($p) => $p->expenses->sum('amount')),
            'avgProgress' => $projects->avg(fn($p) => $p->progressUpdates->sortByDesc('progress_date')->first()?->percentage ?? 0),
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
            'dailyActivity' => $projects->flatMap->dailyReports->sortByDesc('report_date')->take(10)->map(fn($d) => [
                'date' => $d->report_date->format('d M Y'),
                'project' => $d->project->name,
                'staff' => $d->user->name,
                'desc' => $d->work_description,
                'progress' => $d->progress_percentage,
            ]),
        ];
    }
}
