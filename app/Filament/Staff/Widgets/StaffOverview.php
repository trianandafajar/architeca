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
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = $thisMonth->copy()->subMonth();

        $myReports = DailyReport::query()->where('user_id', auth()->id())->whereIn('project_id', $projectIds);
        $myProgress = ProgressUpdate::query()->where('user_id', auth()->id())->whereIn('project_id', $projectIds);
        $reportsThisMonth = (clone $myReports)->where('report_date', '>=', $thisMonth)->count();
        $reportsLastMonth = (clone $myReports)->whereBetween('report_date', [$lastMonth, $thisMonth->copy()->subSecond()])->count();
        $progressThisMonth = (clone $myProgress)->where('progress_date', '>=', $thisMonth)->count();
        $progressLastMonth = (clone $myProgress)->whereBetween('progress_date', [$lastMonth, $thisMonth->copy()->subSecond()])->count();
        $reportsChange = $reportsLastMonth > 0 ? (($reportsThisMonth - $reportsLastMonth) / $reportsLastMonth) * 100 : ($reportsThisMonth > 0 ? 100 : 0);
        $progressChange = $progressLastMonth > 0 ? (($progressThisMonth - $progressLastMonth) / $progressLastMonth) * 100 : ($progressThisMonth > 0 ? 100 : 0);
        $trendIcon = static fn (float $value): string => $value >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $trend = static fn (float $value): string => ($value >= 0 ? '+' : '') . number_format($value, 1) . '% vs previous month';

        return [
            Stat::make('Assigned projects', count($projectIds))
                ->description('Projects assigned to you')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
            Stat::make('Active projects', $this->assignedProjectsQuery()->where('status', 'active')->count())
                ->description('In progress')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('success'),
            Stat::make('My daily reports', $reportsThisMonth)
                ->description($trend($reportsChange))
                ->descriptionIcon($trendIcon($reportsChange))
                ->descriptionColor($reportsChange >= 0 ? 'success' : 'danger')
                ->color('info'),
            Stat::make('My progress updates', $progressThisMonth)
                ->description($trend($progressChange))
                ->descriptionIcon($trendIcon($progressChange))
                ->descriptionColor($progressChange >= 0 ? 'success' : 'danger')
                ->color('warning'),
        ];
    }
}
