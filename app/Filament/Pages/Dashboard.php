<?php

namespace App\Filament\Pages;

use App\Models\DailyReport;
use App\Models\Expense;
use App\Models\ProgressUpdate;
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
        $projects = Project::with(['budgetItems', 'expenses', 'progressUpdates', 'dailyReports.user'])->get();

        $totalBudget = $projects->sum('contract_value');
        $totalExpenses = Expense::sum('amount');

        $latestProgressByProject = $projects->map(function ($p) {
            $latest = $p->progressUpdates->sortByDesc('progress_date')->first();

            return [
                'name' => $p->name,
                'progress' => $latest?->percentage ?? 0,
                'date' => $latest?->progress_date,
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
            'recentActivity' => DailyReport::with(['project', 'user'])
                ->latest('report_date')
                ->take(8)
                ->get(),
            'expensesByCategory' => $expensesByCategory,
            'monthlyExpenses' => $monthlyExpenses,
        ];
    }
}
