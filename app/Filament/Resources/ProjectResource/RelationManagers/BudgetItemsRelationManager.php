<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;

class BudgetItemsRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'budgetItems';

    protected static ?string $title = 'Budgeting';

    protected static ?string $icon = 'heroicon-o-banknotes';

    protected static ?string $recordTitleAttribute = 'item_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_name')
                    ->label('Item Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit')
                    ->label('Unit')
                    ->numeric()
                    ->minValue(1)
                    ->extraInputAttributes([
                        'min' => 1,
                        'inputmode' => 'numeric',
                        'onkeydown' => "return !['e', 'E', '+', '-', '.'].includes(event.key)",
                        'oninput' => 'if (this.value < 1) this.value = 1',
                    ])
                    ->default(0)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Get $get, Set $set): mixed => $set(
                        'total_price',
                        (float) ($get('unit_price') ?? 0) * (float) ($get('unit') ?? 0),
                    )),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->numeric()
                    ->minValue(1)
                    ->extraInputAttributes([
                        'min' => 1,
                        'inputmode' => 'decimal',
                        'onkeydown' => "return !['e', 'E', '+', '-'].includes(event.key)",
                        'oninput' => 'if (this.value < 1) this.value = 1',
                    ])
                    ->prefix('$')
                    ->default(0)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Get $get, Set $set): mixed => $set(
                        'total_price',
                        (float) ($get('unit_price') ?? 0) * (float) ($get('unit') ?? 0),
                    )),
                Forms\Components\TextInput::make('total_price')
                    ->label('Total Price')
                    ->numeric()
                    ->minValue(0)
                    ->extraInputAttributes(['min' => 0])
                    ->prefix('$')
                    ->default(0)
                    ->readOnly()
                    ->dehydrated(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item'),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit'),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('USD'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Add budget item to this project.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Update budget item details.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Deleted budget items cannot be recovered.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Selected budget items will be deleted and cannot be recovered.',
                    ),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
