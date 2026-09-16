<?php

namespace App\Filament\Staff\Widgets;

use App\Filament\Staff\Widgets\Concerns\InteractsWithStaffProjects;
use Filament\Widgets\ChartWidget;

class AssignedProgressChart extends ChartWidget
{
    use InteractsWithStaffProjects;

    protected static ?int $sort = 2;
    protected static bool $isLazy = false;
    protected static string $color = 'primary';
    protected static ?string $heading = 'Project progress';
    protected static ?string $description = 'Progress terakhir dari project yang ditugaskan kepada Anda.';
    protected static ?string $maxHeight = '320px';
    protected int | string | array $columnSpan = ['md' => 1, 'xl' => 1];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $projects = $this->assignedProjectsQuery()->with('progressUpdates')->get();

        return [
            'labels' => $projects->pluck('name')->values()->all(),
            'datasets' => [[
                'label' => 'Progress (%)',
                'data' => $projects->map(fn ($project) => (float) ($project->progressUpdates->sortByDesc('progress_date')->first()?->percentage ?? 0))->values()->all(),
                'backgroundColor' => '#8b4513',
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
