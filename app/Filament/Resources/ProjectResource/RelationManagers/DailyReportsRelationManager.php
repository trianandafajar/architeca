<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;

class DailyReportsRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'dailyReports';

    protected static ?string $title = 'Daily Report';

    protected static ?string $icon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'report_date';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
                Forms\Components\DatePicker::make('report_date')
                    ->label('Tanggal Laporan')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('workers_count')
                    ->label('Jumlah Pekerja')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('progress_percentage')
                    ->label('Progress (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                Forms\Components\Textarea::make('work_description')
                    ->label('Deskripsi Pekerjaan')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('issues')
                    ->label('Kendala / Issues')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('workers_count')
                    ->label('Pekerja'),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge(),
                Tables\Columns\TextColumn::make('work_description')
                    ->label('Deskripsi')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh'),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Tambahkan laporan harian untuk merekam aktivitas project.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\ViewAction::make(),
                    'Lihat detail aktivitas dan kondisi project pada tanggal ini.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Perbarui laporan harian dan kendala yang tercatat.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Laporan harian yang dipilih akan dihapus dan tidak dapat dipulihkan.',
                    ),
                ]),
            ]);

    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
