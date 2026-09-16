<?php

namespace App\Filament\Contractor\Pages;

use App\Filament\Contractor\Widgets\BudgetVsExpenseChart;
use App\Filament\Contractor\Widgets\DailyActivityReport;
use App\Filament\Contractor\Widgets\ProgressChart;
use App\Filament\Contractor\Widgets\ReportsOverview;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string $view = 'filament.contractor.pages.reports';

    protected static ?string $title = 'Laporan';

    protected static ?string $navigationGroup = 'Project';

    protected static ?string $navigationLabel = 'Reports';

    public function getSubheading(): ?string
    {
        return 'Pantau anggaran, progress, dan aktivitas project yang Anda miliki atau ikuti.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReportsOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | string | array
    {
        return 4;
    }

    protected function getFooterWidgets(): array
    {
        return [
            BudgetVsExpenseChart::class,
            ProgressChart::class,
            DailyActivityReport::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | string | array
    {
        return [
            'default' => 1,
            'xl' => 2,
        ];
    }
}
