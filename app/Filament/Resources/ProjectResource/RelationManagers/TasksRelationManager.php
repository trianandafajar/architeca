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
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Task Title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('percentage_weight')
                    ->label('Weight (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->extraInputAttributes(['min' => 0])
                    ->required(),
                Forms\Components\Select::make('assigned_to')
                    ->label('Assignee')
                    ->relationship('user', 'name', modifyQueryUsing: fn(Builder $query) => $query->where('role', 'staff'))
                    ->searchable()
                    ->preload()
                    ->placeholder('Select staff')
                    ->nullable(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('percentage_weight')->label('Weight')->suffix('%')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Assignee')->searchable(),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->limit(50),
                Tables\Columns\ImageColumn::make('evidence_path')
                    ->label('Attachment')
                    ->disk('public')
                    ->height(50)
                    ->width(50),
                Tables\Columns\IconColumn::make('is_completed')
                    ->boolean()
                    ->label('Done'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New project task')
                    ->modalHeading('Create project task')
                    ->modalDescription('Add multiple tasks to this project.')
                    ->form([
                        Forms\Components\Select::make('assigned_to_all')
                            ->label('Assignee for All Tasks')
                            ->relationship('user', 'name', modifyQueryUsing: fn(Builder $query) => $query->where('role', 'staff'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select staff to assign to all')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $tasks = $get('tasks') ?? [];
                                foreach ($tasks as $key => $task) {
                                    $tasks[$key]['assigned_to'] = $state;
                                }
                                $set('tasks', $tasks);
                            }),
                        Forms\Components\Repeater::make('tasks')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Task Title')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('percentage_weight')
                                    ->label('Weight (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required(),
                                Forms\Components\Hidden::make('assigned_to')
                                    ->default(fn($get) => $get('../../assigned_to_all')),
                            ])
                            ->default([[]])
                            ->columns(2)
                            ->createItemButtonLabel('Add Task')
                            ->minItems(1)
                            ->columnSpanFull()
                    ])
                    ->action(function (array $data): void {
                        $tasks = $data['tasks'] ?? [];
                        $assignedTo = $data['assigned_to_all'] ?? null;
                        foreach ($tasks as $taskData) {
                            $taskData['assigned_to'] = $assignedTo;
                            $this->getOwnerRecord()->tasks()->create($taskData);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->label('Task Title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('percentage_weight')
                            ->label('Weight (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assignee')
                            ->relationship('user', 'name', modifyQueryUsing: fn(Builder $query) => $query->where('role', 'staff'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select staff')
                            ->nullable(),
                        Forms\Components\Checkbox::make('is_completed')
                            ->label('Completed'),
                        Forms\Components\FileUpload::make('evidence_path')
                            ->label('Evidence Photo')
                            ->image()
                            ->directory('tasks-evidence'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull(),
                    ]),
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
