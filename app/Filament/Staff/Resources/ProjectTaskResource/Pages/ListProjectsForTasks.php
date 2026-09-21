<?php

namespace App\Filament\Staff\Resources\ProjectTaskResource\Pages;

use App\Filament\Staff\Resources\ProjectTaskResource;
use App\Models\Project;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListProjectsForTasks extends ListRecords
{
    protected static string $resource = ProjectTaskResource::class;

    public function getModel(): string
    {
        return Project::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Project::query()
                    ->whereHas('members', fn (Builder $query) => $query->where('user_id', auth()->id()))
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active', 'completed' => 'success',
                        'planning' => 'warning',
                        'on_hold' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_tasks')
                    ->label('View Tasks')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Project $record): string => ProjectTaskResource::getUrl('project-view', ['project' => $record])),
            ])
            ->bulkActions([]);
    }
}
