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
                    ->label('Tanggal')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'material' => 'Material',
                        'labor' => 'Tenaga Kerja',
                        'equipment' => 'Peralatan',
                        'transport' => 'Transportasi',
                        'other' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh'),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Tambahkan pengeluaran baru dan simpan rincian biayanya.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Perbarui rincian pengeluaran project ini.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Data pengeluaran yang dihapus tidak dapat dipulihkan.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Pengeluaran yang dipilih akan dihapus dan tidak dapat dipulihkan.',
                    ),
                ]),
            ]);
    }
    public function isReadOnly(): bool
    {
        return false;
    }
}
