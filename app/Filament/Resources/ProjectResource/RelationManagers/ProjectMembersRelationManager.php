<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;

class ProjectMembersRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'members';

    protected static ?string $title = 'Project Members';

    protected static ?string $icon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'user_id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->options(User::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('role')
                    ->label('Role')
                    ->options([
                        'owner' => 'Owner',
                        'manager' => 'Manager',
                        'supervisor' => 'Supervisor',
                        'worker' => 'Worker',
                        'viewer' => 'Viewer',
                    ])
                    ->default('worker'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Add member and define their role in this project.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Update member role on this project.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Removed members no longer have access through this project.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Selected members will be removed from this project.',
                    ),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}