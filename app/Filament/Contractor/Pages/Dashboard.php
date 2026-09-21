<?php

namespace App\Filament\Contractor\Pages;

use App\Models\DailyReport;
use App\Models\Project;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Contractor\Widgets\Concerns\InteractsWithContractorProjects;

class Dashboard extends BaseDashboard
{
    use InteractsWithContractorProjects;

    protected static string $view = 'filament.contractor.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    public function getViewData(): array
    {
        $user = auth()->user();

        $projects = Project::query()
            ->where(function ($query) use ($user): void {
                $query
                    ->where('owner_id', $user->id)
                    ->orWhereHas('members', fn($members) => $members->where('user_id', $user->id));
            })
            ->with([
                'expenses',
                'members',
                'attachments',
                'tasks',
            ])
            ->latest('created_at')
            ->get();

        $projectIds = $projects->modelKeys();
        $totalBudget = (float) $projects->sum('contract_value');
        $totalExpenses = (float) $projects->sum(fn(Project $project) => $project->expenses->sum('amount'));

        $latestProgress = $projects->map(function (Project $p) {
            $completedWeight = $p->tasks->where('is_completed', true)->sum('percentage_weight');
            $totalWeight = $p->tasks->sum('percentage_weight');
            $progress = $totalWeight > 0 ? ($completedWeight / $totalWeight) * 100 : 0;
            return [
                'project' => $p,
                'name' => $p->name,
                'percentage' => $progress,
            ];
        });

        $budgetUsedPercent = $totalBudget > 0 ? round(($totalExpenses / $totalBudget) * 100, 1) : 0;

        $avgProgress = round($latestProgress->avg('percentage') ?? 0);

        $budgetUsedPercent = $totalBudget > 0 ? round(($totalExpenses / $totalBudget) * 100, 1) : 0;

        $recentActivity = DailyReport::with(['project', 'user'])
            ->whereIn('project_id', $this->accessibleProjectIds())
            ->latest('report_date')
            ->take(8)
            ->get();

        return [
            'projects' => $projects,
            'totalProjects' => $projects->count(),
            'activeProjects' => $projects->where('status', 'active')->count(),
            'totalBudget' => $totalBudget,
            'totalExpenses' => $totalExpenses,
            'budgetUsedPercent' => $budgetUsedPercent,
            'avgProgress' => $avgProgress,
            'latestProgress' => $latestProgress,
            'recentActivity' => $recentActivity,
        ];
    }
}
