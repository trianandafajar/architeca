<?php

namespace App\Filament\Contractor\Widgets;

use App\Filament\Contractor\Widgets\Concerns\InteractsWithContractorProjects;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ReportsOverview extends StatsOverviewWidget
{
    use InteractsWithContractorProjects;

    protected static ?int $sort = 1;
    protected static bool $isLazy = true;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $projects = $this->accessibleProjectsQuery()->with(['expenses', 'progressUpdates'])->get();
        $totalBudget = (float) $projects->sum('contract_value');
        $totalExpenses = (float) $projects->sum(fn ($project) => $project->expenses->sum('amount'));
        $latestProgress = $projects->map(fn ($project) => $project->progressUpdates->sortByDesc('progress_date')->first()?->percentage ?? 0);
        $budgetUsed = $totalBudget > 0 ? round(($totalExpenses / $totalBudget) * 100, 1) : 0;

        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = $currentMonth->copy()->subMonth();
        $expenses = $projects->flatMap->expenses;
        $currentMonthExpenses = (float) $expenses->filter(fn ($expense) => $expense->expense_date?->isSameMonth($currentMonth))->sum('amount');
        $previousMonthExpenses = (float) $expenses->filter(fn ($expense) => $expense->expense_date?->isSameMonth($previousMonth))->sum('amount');
        $expenseChange = $previousMonthExpenses > 0
            ? (($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100
            : ($currentMonthExpenses > 0 ? 100 : 0);

        $currentMonthProjects = $projects->filter(fn ($project) => $project->created_at?->isSameMonth($currentMonth));
        $previousProjectCount = max(0, $projects->count() - $currentMonthProjects->count());
        $previousContractValue = max(0, $totalBudget - (float) $currentMonthProjects->sum('contract_value'));
        $projectChange = $previousProjectCount > 0
            ? (($projects->count() - $previousProjectCount) / $previousProjectCount) * 100
            : ($projects->count() > 0 ? 100 : 0);
        $contractChange = $previousContractValue > 0
            ? (($totalBudget - $previousContractValue) / $previousContractValue) * 100
            : ($totalBudget > 0 ? 100 : 0);

        $previousProgress = $projects->map(function ($project) {
            return $project->progressUpdates->sortByDesc('progress_date')->values()->get(1)?->percentage
                ?? $project->progressUpdates->sortByDesc('progress_date')->first()?->percentage
                ?? 0;
        });
        $progressChange = (float) ($latestProgress->avg() ?? 0) - (float) ($previousProgress->avg() ?? 0);
        $trend = static fn (float $value): string => ($value >= 0 ? '+' : '') . number_format($value, 1) . '% vs previous month';
        $pointsTrend = static fn (float $value): string => ($value >= 0 ? '+' : '') . number_format($value, 1) . ' pts vs previous update';
        $trendIcon = static fn (float $value): string => $value >= 0
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';

        return [
            Stat::make('My projects', $projects->count())
                ->description($trend($projectChange))
                ->descriptionIcon($trendIcon($projectChange))
                ->descriptionColor($projectChange >= 0 ? 'success' : 'danger')
                ->color('primary')
                ->icon('heroicon-m-briefcase'),
            Stat::make('Contract value', number_format($totalBudget, 0, ',', '.'))
                ->description($trend($contractChange))
                ->descriptionIcon($trendIcon($contractChange))
                ->descriptionColor($contractChange >= 0 ? 'success' : 'danger')
                ->color('info')
                ->icon('heroicon-m-banknotes'),
            Stat::make('Total expenses', number_format($totalExpenses, 0, ',', '.'))
                ->description($trend($expenseChange))
                ->descriptionIcon($trendIcon($expenseChange))
                ->descriptionColor($expenseChange > 0 ? 'danger' : 'success')
                ->color($budgetUsed > 90 ? 'danger' : 'warning')
                ->icon('heroicon-m-receipt-percent'),
            Stat::make('Average progress', round($latestProgress->avg() ?? 0) . '%')
                ->description($pointsTrend($progressChange))
                ->descriptionIcon($progressChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($progressChange >= 0 ? 'success' : 'danger')
                ->color('success')
                ->icon('heroicon-m-chart-bar'),
        ];
    }
}
