<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\HasProjectAttachments;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProgressUpdatesRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;
    use HasProjectAttachments;

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
                    ->label('Date')
                    ->default(now())
                    ->required(),
                Forms\Components\ViewField::make('percentage')
                    ->label('Percentage (%)')
                    ->view('filament.forms.components.range-slider')
                    ->default(0)
                    ->rules(['required', 'numeric', 'min:0', 'max:100'])
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
                $this->attachmentsUpload('progress-updates'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('attachments'))
            ->columns([
                Tables\Columns\TextColumn::make('progress_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        (float) $state >= 100 => 'success',
                        (float) $state >= 50 => 'warning',
                        (float) $state > 0 => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('By'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50),
                $this->attachmentsColumn(),
            ])
            ->defaultSort('progress_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Record latest progress update for this project.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Update progress notes for this project.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Progress data that has been deleted cannot be recovered.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Selected progress data will be deleted and cannot be recovered.',
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
