<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;

class ExpensesRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'expenses';

    protected static ?string $title = 'Expenses';

    protected static ?string $icon = 'heroicon-o-banknotes';

    protected static ?string $recordTitleAttribute = 'description';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
                Forms\Components\DatePicker::make('expense_date')
                    ->label('Date')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('category')
                    ->label('Category')
                    ->options([
                        'material' => 'Material',
                        'labor' => 'Labor',
                        'equipment' => 'Equipment',
                        'transport' => 'Transport',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('By'),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Add new expense and save cost details.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Update expense details for this project.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Expense data that has been deleted cannot be recovered.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Selected expenses will be deleted and cannot be recovered.',
                    ),
                ]),
            ]);
    }
    public function isReadOnly(): bool
    {
        return false;
    }
}