<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationLabel = 'Projects';
    protected static ?string $modelLabel = 'Project';
    protected static ?string $pluralModelLabel = 'Projects';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('members', fn (Builder $members): Builder => $members->where('user_id', auth()->id()));
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Project $record): string => static::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->branch?->name
                            ? "{$state}, {$record->branch->name}"
                            : $state
                    )
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contract_value')
                    ->label('Contract Value')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Period')
                    ->formatStateUsing(fn ($record) =>
                        collect([
                            $record->start_date?->format('M d, Y'),
                            $record->end_date?->format('M d, Y'),
                        ])->filter()->join(' - ')
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'planning' => 'warning',
                        'on_hold' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'planning' => 'Planning',
                    'active' => 'Active',
                    'on_hold' => 'On Hold',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->icon('heroicon-o-eye'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            
        \App\Filament\Staff\Resources\ProjectResource\RelationManagers\TasksRelationManager::class,
            \App\Filament\Staff\Resources\ProjectResource\RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Project overview')
                ->description('Project information and status.')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('name')->label('Project name')->weight('bold'),
                        TextEntry::make('client_name')->label('Client')->placeholder('—'),
                        TextEntry::make('status')->label('Status')->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                            ->color(fn (string $state): string => match ($state) {
                                'active', 'completed' => 'success',
                                'planning' => 'warning',
                                'on_hold' => 'info',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('location')->label('Location')->placeholder('—'),
                        TextEntry::make('start_date')->label('Period')->date('d M Y')
                            ->formatStateUsing(fn ($state, $record): string => $state
                                ? $state->format('d M Y') . ' - ' . ($record->end_date?->format('d M Y') ?? '—')
                                : '—'),
                    ]),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'view' => Pages\ViewProject::route('/{record}'),
        ];
    }
}
