<?php

namespace App\Filament\Staff\Pages;

use App\Filament\Staff\Widgets\AssignedProgressChart;
use App\Filament\Staff\Widgets\MyDailyReports;
use App\Filament\Staff\Widgets\StaffOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.staff.pages.dashboard';

    protected static ?string $navigationGroup = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getSubheading(): ?string
    {
        return 'Fokus pada project, progress, dan laporan kerja Anda.';
    }

    protected function getHeaderWidgets(): array
    {
        return [StaffOverview::class];
    }

    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 4;
    }

    protected function getFooterWidgets(): array
    {
        return [
            AssignedProgressChart::class,
            MyDailyReports::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | string | array
    {
        return ['default' => 1, 'xl' => 2];
    }
}
