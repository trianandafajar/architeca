<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tasks';

    protected static ?string $icon = 'heroicon-o-clipboard';

    public function form(Form $form): Form
    {
        $fields = [];

        $fields[] = Forms\Components\TextInput::make('title')
            ->label('Task Title')
            ->required()
            ->maxLength(255);

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

        $fields[] = Forms\Components\Toggle::make('is_completed')
            ->label('Completed')
            ->default(false)
            ->onColor('success')
            ->offColor('danger')
            ->inline(false);
        $fields[] = Forms\Components\FileUpload::make('evidence_path')
            ->label('Evidence Photo')
            ->image()
            ->directory('tasks-evidence')
            ->columnSpanFull();
        $fields[] = Forms\Components\Textarea::make('notes')
            ->label('Notes')
            ->columnSpanFull();

        return $form->schema($fields)->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('percentage_weight')->label('Weight')->suffix('%')->sortable(),
                Tables\Columns\ToggleColumn::make('is_completed')->onColor('success')->offColor('danger')->label('Done'),
                Tables\Columns\TextColumn::make('user.name')->label('Assignee')->searchable(),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->limit(50),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->color('primary'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function canCreate(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'contractor']);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
