<?php

namespace App\Filament\Staff\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use App\Models\DailyReport;

class MyDailyReports extends TableWidget
{
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = ['md' => 1, 'xl' => 1];

    public function table(Table $table): Table
    {
        return $table
            ->heading('My recent reports')
            ->description('Daily reports you have submitted.')
            ->query(DailyReport::query()->with('project')->where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('report_date')->label('Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('project.name')->label('Project')->limit(28)->searchable(),
                Tables\Columns\TextColumn::make('workers_count')->label('Workers'),
                Tables\Columns\TextColumn::make('progress_percentage')->label('Progress')->suffix('%')->badge()->color('info'),
                Tables\Columns\TextColumn::make('work_description')->label('Description')->limit(42)->placeholder('—'),
            ])
            ->defaultSort('report_date', 'desc')
            ->paginated([5, 10, 25]);
    }
}
