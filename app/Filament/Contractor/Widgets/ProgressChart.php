<?php

namespace App\Filament\Contractor\Widgets;

use App\Filament\Contractor\Widgets\Concerns\InteractsWithContractorProjects;
use Filament\Widgets\ChartWidget;

class ProgressChart extends ChartWidget
{
    use InteractsWithContractorProjects;

    protected static ?int $sort = 3;
    protected static bool $isLazy = false;
    protected static string $color = 'success';
    protected static ?string $heading = 'Progress by project';
    protected static ?string $description = 'Progress terakhir dari setiap project yang Anda akses.';
    protected static ?string $maxHeight = '320px';
    protected int | string | array $columnSpan = ['md' => 1, 'xl' => 1];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $projects = $this->accessibleProjectsQuery()->with('progressUpdates')->get();

        return [
            'labels' => $projects->pluck('name')->values()->all(),
            'datasets' => [[
                'label' => 'Progress (%)',
                'data' => $projects->map(fn ($project) => (float) ($project->progressUpdates->sortByDesc('progress_date')->first()?->percentage ?? 0))->values()->all(),
                'backgroundColor' => '#2f855a',
                'borderRadius' => 6,
                'maxBarThickness' => 42,
            ]],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'max' => 100],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
