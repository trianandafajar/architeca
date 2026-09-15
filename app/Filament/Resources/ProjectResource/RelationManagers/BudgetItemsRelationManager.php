<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'budgetItems';

    protected static ?string $title = 'RAB / Budget';

    protected static ?string $recordTitleAttribute = 'item_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_name')
                    ->label('Nama Item')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('unit')
                    ->label('Satuan')
                    ->maxLength(50),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('total_price')
                    ->label('Total Harga')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Harga Satuan')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR'),
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