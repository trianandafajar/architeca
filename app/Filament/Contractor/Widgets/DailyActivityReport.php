<?php

namespace App\Filament\Contractor\Widgets;

use App\Filament\Contractor\Widgets\Concerns\InteractsWithContractorProjects;
use App\Models\DailyReport;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DailyActivityReport extends TableWidget
{
    use InteractsWithContractorProjects;

    protected static ?int $sort = 4;
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = ['md' => 1, 'xl' => 1];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Daily activity')
            ->description('Latest daily reports from projects you can access.')
            ->query(DailyReport::query()->with(['project', 'user'])->whereIn('project_id', $this->accessibleProjectIds()))
            ->columns([
                Tables\Columns\TextColumn::make('report_date')->label('Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('project.name')->label('Project')->limit(24)->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('By'),
                Tables\Columns\TextColumn::make('work_description')->label('Description')->limit(42)->placeholder('—'),
                Tables\Columns\TextColumn::make('progress_percentage')->label('Progress')->suffix('%')->badge()->color('info'),
            ])
            ->defaultSort('report_date', 'desc')
            ->paginated([5, 10, 25]);
    }
}
