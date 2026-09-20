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

    public static function canAccess(): bool
    {
        return false;
    }

    protected static ?string $navigationLabel = 'Reports';

    public function getSubheading(): ?string
    {
        return 'Monitor the budget, progress, and activity of projects you own or follow.';
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
