<?php

namespace App\Filament\Contractor\Widgets;

use App\Filament\Contractor\Widgets\Concerns\InteractsWithContractorProjects;
use Filament\Widgets\ChartWidget;

class BudgetVsExpenseChart extends ChartWidget
{
    use InteractsWithContractorProjects;

    protected static ?int $sort = 2;
    protected static bool $isLazy = false;
    protected static string $color = 'primary';
    protected static ?string $heading = 'Budget vs expenses';
    protected static ?string $description = 'This chart shows the budget vs expenses for all your projects.';
    protected static ?string $maxHeight = '320px';
    protected int | string | array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $projects = $this->accessibleProjectsQuery()->with('expenses')->get();

        return [
            'labels' => $projects->pluck('name')->values()->all(),
            'datasets' => [
                [
                    'label' => 'Contract value',
                    'data' => $projects->map(fn ($project) => (float) $project->contract_value)->values()->all(),
                    'backgroundColor' => '#c0875a',
                    'borderRadius' => 6,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $projects->map(fn ($project) => (float) $project->expenses->sum('amount'))->values()->all(),
                    'backgroundColor' => '#8b4513',
                    'borderRadius' => 6,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                'y' => ['beginAtZero' => true],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
