<?php

namespace App\Filament\Contractor\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Filament\Resources\ProjectResource\RelationManagers\BudgetItemsRelationManager as BaseBudgetItemsRelationManager;

class BudgetItemsRelationManager extends BaseBudgetItemsRelationManager
{
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
}
