<?php

namespace App\Filament\Staff\Pages;

use App\Models\Project;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.staff.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    public function getViewData(): array
    {
        $user = auth()->user();

        $projects = Project::query()
            ->whereHas('members', fn($query) => $query->where('user_id', $user->id))
            ->with(['tasks', 'expenses'])
            ->latest('created_at')
            ->get();

        $totalBudget = $projects->sum('contract_value');
        $totalExpenses = $projects->sum(fn($p) => $p->expenses->sum('amount'));

        $latestProgressByProject = $projects->map(function ($p) use ($user) {
            $userTasks = $p->tasks->where('assigned_to', $user->id);
            $completedWeight = $userTasks->where('is_completed', true)->sum('percentage_weight');
            $totalWeight = $userTasks->sum('percentage_weight');
            $progress = $totalWeight > 0 ? ($completedWeight / $totalWeight) * 100 : 0;
            return [
                'name' => $p->name,
                'progress' => $progress,
                'status' => $p->status,
                'client' => $p->client_name,
            ];
        });

        $assignedProjectIds = $projects->pluck('id')->all();
        $assignedTasks = \App\Models\ProjectTask::whereIn('project_id', $assignedProjectIds)
            ->where('assigned_to', $user->id)
            ->get();
        $totalTasks = $assignedTasks->count();
        $completedTasks = $assignedTasks->where('is_completed', true)->count();
        $taskProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return [
            'totalProjects' => $projects->count(),
            'activeProjects' => $projects->where('status', 'active')->count(),
            'totalBudget' => $totalBudget,
            'totalExpenses' => $totalExpenses,
            'budgetUsedPercent' => $totalBudget > 0 ? round(($totalExpenses / $totalBudget) * 100, 1) : 0,
            'avgProgress' => round($latestProgressByProject->avg('progress') ?? 0),
            'recentProjects' => $projects->take(5),
            'latestProgress' => $latestProgressByProject,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'taskProgress' => $taskProgress,
        ];
    }
}
