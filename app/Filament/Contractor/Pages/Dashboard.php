<?php

namespace App\Filament\Contractor\Pages;

use App\Models\DailyReport;
use App\Models\Project;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
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
            ])
            ->latest('created_at')
            ->get();

        $projectIds = $projects->modelKeys();
        $totalBudget = (float) $projects->sum('contract_value');
        $totalExpenses = (float) $projects->sum(fn(Project $project) => $project->expenses->sum('amount'));

        $latestProgress = collect();

        return [
            'projects' => $projects,
            'totalProjects' => $projects->count(),
            'activeProjects' => $projects->where('status', 'active')->count(),
            'totalBudget' => $totalBudget,
            'totalExpenses' => $totalExpenses,
            'budgetUsedPercent' => $totalBudget > 0 ? round(($totalExpenses / $totalBudget) * 100, 1) : 0,
            'avgProgress' => 0,
            'latestProgress' => $latestProgress,
            // 'recentActivity' => DailyReport::query()
            //     ->with(['project', 'user'])
            //     ->when($projectIds, fn($query) => $query->whereIn('project_id', $projectIds), fn($query) => $query->whereRaw('1 = 0'))
            //     ->latest('report_date')
            //     ->take(6)
            //     ->get(),
        ];
    }
}
