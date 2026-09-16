<?php

namespace App\Filament\Contractor\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\FiltersProjectMemberUserOptions;

class ProjectMembersRelationManager extends RelationManager
{
    use FiltersProjectMemberUserOptions;

    protected static string $relationship = 'members';

    protected static ?string $title = 'Project Members';

    protected static ?string $recordTitleAttribute = 'user_id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->options(fn (Get $get): array => $this->getAvailableProjectMemberUserOptions($get))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Hanya user yang belum menjadi anggota project yang ditampilkan.'),
                Forms\Components\Select::make('role')
                    ->label('Peran')
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
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
