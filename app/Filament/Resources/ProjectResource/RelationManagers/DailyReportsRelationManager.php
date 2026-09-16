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

    protected static ?string $title = 'Daily Reports';

    protected static ?string $icon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'report_date';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
                Forms\Components\DatePicker::make('report_date')
                    ->label('Report Date')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('workers_count')
                    ->label('Number of Workers')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('progress_percentage')
                    ->label('Progress (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                Forms\Components\Textarea::make('work_description')
                    ->label('Work Description')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('issues')
                    ->label('Issues / Obstacles')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('workers_count')
                    ->label('Workers'),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge(),
                Tables\Columns\TextColumn::make('work_description')
                    ->label('Description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('By'),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Add a daily report to record project activity.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\ViewAction::make(),
                    'View project activity and status on this date.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Update daily report and recorded issues.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Selected daily reports will be deleted and cannot be recovered.',
                    ),
                ]),
            ]);

    }

    public function isReadOnly(): bool
    {
        return false;
    }
}