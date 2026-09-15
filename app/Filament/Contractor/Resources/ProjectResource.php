<?php

namespace App\Filament\Contractor\Resources;

use App\Filament\Contractor\Resources\ProjectResource\Pages;
use App\Filament\Contractor\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Project';

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $modelLabel = 'Project';

    protected static ?string $pluralModelLabel = 'Projects';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('members', fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Project')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Project')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('client_name')
                            ->label('Nama Klien')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contract_value')
                            ->label('Nilai Kontrak')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'planning' => 'Planning',
                                'active' => 'Aktif',
                                'on_hold' => 'Ditahan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('planning'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Klien')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contract_value')
                    ->label('Nilai Kontrak')
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
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Aktif',
                        'on_hold' => 'Ditahan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}