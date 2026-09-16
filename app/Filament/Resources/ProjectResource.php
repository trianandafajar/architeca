<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Project';

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Projects';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getProjectFormSchema());
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function getProjectFormSchema(): array
    {
        return [
                Forms\Components\Section::make('Project Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Project Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g.: Building A Construction'),
                        Forms\Components\TextInput::make('client_name')
                            ->label('Client Name')
                            ->maxLength(255)
                            ->placeholder('client name'),
                        Forms\Components\Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('location')
                            ->label('Location')
                            ->maxLength(255)
                            ->placeholder('project location'),
                        Forms\Components\TextInput::make('contract_value')
                            ->label('Contract Value')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'planning' => 'Planning',
                                'active' => 'Active',
                                'on_hold' => 'On Hold',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('planning'),
                    ])
                    ->columns(2),
        ];
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
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contract_value')
                    ->label('Contract Value')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'planning' => 'warning',
                        'on_hold' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->label('Branch')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BudgetItemsRelationManager::class,
            RelationManagers\ProgressUpdatesRelationManager::class,
            RelationManagers\DailyReportsRelationManager::class,
            RelationManagers\ExpensesRelationManager::class,
            RelationManagers\ProjectMembersRelationManager::class,
            RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Project Overview')
                    ->description('Project information summary and status.')
                    ->headerActions([
                        InfolistAction::make('edit')
                            ->label('Edit Project')
                            ->icon('heroicon-m-pencil-square')
                            ->button()
                            ->color('gray')
                            ->extraAttributes(['class' => 'architeca-edit-project-action'])
                            ->url(fn (Project $record): string => static::getUrl('edit', ['record' => $record]))
                            ->visible(fn (Project $record): bool => static::canEdit($record)),
                    ])
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Project Name')
                                    ->weight('bold'),
                                TextEntry::make('client_name')
                                    ->label('Client')
                                    ->placeholder('-'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'active' => 'Active',
                                        'planning' => 'Planning',
                                        'on_hold' => 'On Hold',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                        default => ucfirst($state),
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'active', 'completed' => 'success',
                                        'planning' => 'warning',
                                        'on_hold' => 'info',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('location')
                                    ->label('Location')
                                    ->placeholder('-'),
                                TextEntry::make('contract_value')
                                    ->label('Contract Value')
                                    ->money('IDR'),
                                TextEntry::make('start_date')
                                    ->label('Period')
                                    ->date('d M Y')
                                    ->formatStateUsing(fn ($state, $record): string => $state
                                        ? $state->format('d M Y') . ' - ' . ($record->end_date?->format('d M Y') ?? '-')
                                        : '-'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
