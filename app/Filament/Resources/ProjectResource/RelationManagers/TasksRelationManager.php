<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tasks';

    protected static ?string $icon = 'heroicon-o-clipboard';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        $fields = [];

        // Title – always shown, editable only by admin/contractor
        $titleField = Forms\Components\TextInput::make('title')
            ->label('Task Title')
            ->required()
            ->maxLength(255);
        if (! in_array(auth()->user()->role, ['admin', 'contractor'])) {
            $titleField = $titleField->disabled();
        }
        $fields[] = $titleField;

        // Weight and assignee – only for admin/contractor
        if (in_array(auth()->user()->role, ['admin', 'contractor'])) {
            $fields[] = Forms\Components\TextInput::make('percentage_weight')
                ->label('Weight (%)')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->required();
            $fields[] = Forms\Components\Select::make('assigned_to')
                ->label('Assignee')
                ->relationship('user', 'name')
                ->searchable()
                ->placeholder('Select staff')
                ->nullable();
        }

        // Completed and notes – always editable
        $fields[] = Forms\Components\Toggle::make('is_completed')
            ->label('Completed')
            ->default(false)
            ->onColor('success')
            ->offColor('danger')
            ->inline(false);
        $fields[] = Forms\Components\Textarea::make('notes')
            ->label('Notes')
            ->columnSpanFull();

        return $form->schema($fields)->columns(2);
    }



    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('percentage_weight')
                    ->label('Weight')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Done')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assignee')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50),
            ])
            ->defaultSort('title')
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Create a new task for this project.'
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Edit task details.'
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Delete this task.'
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Delete selected tasks.'
                    ),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return auth()->user()->role === 'staff';
    }
}
