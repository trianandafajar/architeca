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
use Illuminate\Support\Collection;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 2;

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
                        ->nullable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('code')->required(),
                            Forms\Components\TextInput::make('location')->nullable(),
                            Forms\Components\Textarea::make('description')->nullable(),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active')
                                ->onColor('success')
                                ->offColor('danger')
                                ->default(true)
                                ->inline(false),
                        ]),
                    Forms\Components\TextInput::make('location')
                        ->label('Location')
                        ->maxLength(255)
                        ->placeholder('project location'),
                    Forms\Components\TextInput::make('contract_value')
                        ->label('Contract Value')
                        ->numeric()
                        ->prefix('$')
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
            ->recordUrl(fn(Project $record): string => static::getUrl('view', ['record' => $record]))
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
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
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
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary'),
                    Tables\Actions\Action::make('changeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-tag')
                        ->color('warning')
                        ->fillForm(fn(Project $record): array => ['status' => $record->status])
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->native(false)
                                ->options([
                                    'planning' => 'Planning',
                                    'active' => 'Active',
                                    'on_hold' => 'On Hold',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                        ])
                        ->action(fn(Project $record, array $data) => $record->update(['status' => $data['status']])),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->icon('heroicon-o-trash'),
                    Tables\Actions\BulkAction::make('changeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-tag')
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        })
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->native(false)
                                ->options([
                                    'planning' => 'Planning',
                                    'active' => 'Active',
                                    'on_hold' => 'On Hold',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                        ])
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
            RelationManagers\BudgetItemsRelationManager::class,
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
                            ->url(fn(Project $record): string => static::getUrl('edit', ['record' => $record]))
                            ->visible(fn(Project $record): bool => static::canEdit($record)),
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
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'active' => 'Active',
                                        'planning' => 'Planning',
                                        'on_hold' => 'On Hold',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                        default => ucfirst($state),
                                    })
                                    ->color(fn(string $state): string => match ($state) {
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
                                    ->money('USD'),
                                TextEntry::make('start_date')
                                    ->label('Period')
                                    ->date('d M Y')
                                    ->formatStateUsing(fn($state, $record): string => $state
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
