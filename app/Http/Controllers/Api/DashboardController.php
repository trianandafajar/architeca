<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DailyReportResource;
use App\Models\DailyReport;
use App\Models\Expense;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->hasRole('staff') || $user->can('view_any_project'), 403);
        $projects = Project::query()->availableTo($user)->withCount('tasks')->get();
        $projectIds = $projects->modelKeys();
        $tasks = ProjectTask::query()->whereIn('project_id', $projectIds)
            ->get(['id', 'project_id', 'percentage_weight', 'is_completed', 'assigned_to']);
        $myTasks = $tasks->where('assigned_to', $user->id);
        $reports = DailyReport::query()->whereIn('project_id', $projectIds)
            ->when($user->hasRole('staff'), fn ($query) => $query->where('user_id', $user->id))
            ->with(['project:id,name', 'user:id,name', 'attachments.user:id,name'])
            ->latest('report_date')->limit(8)->get();

        $completedWeight = (float) $tasks->where('is_completed', true)->sum('percentage_weight');
        $totalWeight = (float) $tasks->sum('percentage_weight');
        $data = [
            'role' => $user->role,
            'projects_count' => $projects->count(),
            'active_projects_count' => $projects->where('status', 'active')->count(),
            'project_tasks_count' => $tasks->count(),
            'tasks_count' => $user->hasRole('staff') ? $myTasks->count() : $tasks->count(),
            'completed_tasks_count' => $user->hasRole('staff')
                ? $myTasks->where('is_completed', true)->count()
                : $tasks->where('is_completed', true)->count(),
            'progress_percentage' => $totalWeight > 0 ? round($completedWeight / $totalWeight * 100, 2) : 0,
            'recent_daily_reports' => DailyReportResource::collection($reports),
        ];

        if (! $user->hasRole('staff')) {
            $expenses = Expense::query()->whereIn('project_id', $projectIds)->get(['category', 'amount', 'expense_date']);
            $data += [
                'contract_value_total' => (float) $projects->sum('contract_value'),
                'expenses_total' => (float) $expenses->sum('amount'),
                'expenses_by_category' => $expenses->groupBy('category')->map(fn ($items, $category) => [
                    'category' => $category,
                    'amount' => (float) $items->sum('amount'),
                ])->values(),
                'monthly_expenses' => $expenses->groupBy(fn (Expense $expense) => $expense->expense_date->format('Y-m'))
                    ->map(fn ($monthExpenses, $month) => [
                        'month' => $month,
                        'amount' => (float) $monthExpenses->sum('amount'),
                    ])->values(),
            ];
        }

        return response()->json(['data' => $data]);
    }
}
