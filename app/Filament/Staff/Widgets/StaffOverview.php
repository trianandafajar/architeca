<?php

namespace App\Filament\Staff\Widgets;

use App\Filament\Staff\Widgets\Concerns\InteractsWithStaffProjects;
use App\Models\DailyReport;
use App\Models\ProgressUpdate;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StaffOverview extends StatsOverviewWidget
{
    use InteractsWithStaffProjects;

    protected static ?int $sort = 1;
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';
    protected ?string $heading = 'Work overview';
    protected ?string $description = 'Operational information for projects you are working on.';

    protected function getStats(): array
    {
        $projectIds = $this->assignedProjectIds();
        
        $totalTasks = \App\Models\ProjectTask::whereIn('project_id', $projectIds)->count();
        $completedTasks = \App\Models\ProjectTask::whereIn('project_id', $projectIds)->where('is_completed', true)->count();
        $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        $myTasks = \App\Models\ProjectTask::where('assigned_to', auth()->id());
        $myTasksTotal = (clone $myTasks)->count();
        $myTasksCompleted = (clone $myTasks)->where('is_completed', true)->count();

        return [
            Stat::make('Assigned projects', count($projectIds))
                ->description('Projects assigned to you')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
            Stat::make('Total Project Progress', number_format($progress, 0) . '%')
                ->description('Aggregate completion')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
            Stat::make('My Tasks', $myTasksCompleted . ' / ' . $myTasksTotal)
                ->description('Completed tasks')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
