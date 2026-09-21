<?php

namespace App\Filament\Staff\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.staff.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    public function getSubheading(): ?string
    {
        return 'Overview of your assigned projects and daily reports.';
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 4;
    }

    protected function getFooterWidgets(): array
    {
        return [
            //
        ];
    }

    public function getFooterWidgetsColumns(): int | string | array
    {
        return ['default' => 1, 'xl' => 2];
    }
}
