<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;

class ProgressUpdatesRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'progressUpdates';

    protected static ?string $title = 'Progress';

    protected static ?string $icon = 'heroicon-o-chart-bar';

    protected static ?string $recordTitleAttribute = 'notes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
                Forms\Components\DatePicker::make('progress_date')
                    ->label('Tanggal')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('percentage')
                    ->label('Persentase (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
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
                Tables\Columns\TextColumn::make('progress_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        (float) $state >= 100 => 'success',
                        (float) $state >= 50 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50),
            ])
            ->defaultSort('progress_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Catat pembaruan progress terbaru untuk project ini.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Perbarui catatan progress project ini.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Data progress yang dihapus tidak dapat dipulihkan.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Data progress yang dipilih akan dihapus dan tidak dapat dipulihkan.',
                    ),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
