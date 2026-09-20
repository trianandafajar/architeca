<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Project;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    public function getViewData(): array
    {
        $projects = Project::with(['budgetItems', 'expenses', 'tasks'])->get();

        $totalBudget = $projects->sum('contract_value');
        $totalExpenses = Expense::sum('amount');

        $latestProgressByProject = $projects->map(function ($p) {
            $completedWeight = $p->tasks->sum('percentage_weight') * ($p->tasks->where('is_completed', true)->count() > 0 ? 1 : 0);
            $totalWeight = $p->tasks->sum('percentage_weight');
            $progress = $totalWeight > 0 ? ($completedWeight / $totalWeight) * 100 : 0;
            return [
                'name' => $p->name,
                'progress' => $progress,
                'date' => now(),
            ];
        });

        $categoryLabels = [
            'material' => 'Material',
            'labor' => 'Labor',
            'equipment' => 'Equipment',
            'transport' => 'Transport',
            'other' => 'Other',
        ];

        $expensesByCategory = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn($row) => [
                'label' => $categoryLabels[$row->category] ?? ucfirst($row->category),
                'value' => (float) $row->total,
            ])
            ->values();

        $monthlyExpenses = Expense::select(
            DB::raw("DATE_FORMAT(expense_date, '%Y-%m') as month"),
            DB::raw('SUM(amount) as total')
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => [
                'label' => Carbon::createFromFormat('Y-m', $row->month)->format('M'),
                'value' => (float) $row->total,
            ])
            ->values();

        return [
            'totalProjects' => $projects->count(),
            'activeProjects' => $projects->where('status', 'active')->count(),
            'totalBudget' => $totalBudget,
            'totalExpenses' => $totalExpenses,
            'budgetUsedPercent' => $totalBudget > 0 ? round(($totalExpenses / $totalBudget) * 100, 1) : 0,
            'avgProgress' => round($latestProgressByProject->avg('progress') ?? 0),
            'recentProjects' => $projects->sortByDesc('created_at')->take(5),
            'latestProgress' => $latestProgressByProject,
            // 'recentActivity' => DailyReport::with(['project', 'user'])
            //     ->latest('report_date')
            //     ->take(8)
            //     ->get(),
            'expensesByCategory' => $expensesByCategory,
            'monthlyExpenses' => $monthlyExpenses,
        ];
    }
}
