<?php

namespace App\Filament\Staff\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tasks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Task Title')
                    ->required()
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Checkbox::make('is_completed')
                    ->label('Completed')
                    ->default(false)
                    ->inline(false)
                    ->rules([
                        fn (\Filament\Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            if ($value && (empty($get('evidence_path')) || empty($get('notes')))) {
                                $fail('Upload evidence and fill notes before marking as completed.');
                            }
                        },
                    ]),
                Forms\Components\FileUpload::make('evidence_path')
                    ->label('Evidence Photo')
                    ->image()
                    ->directory('tasks-evidence')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('assigned_to', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Task')->searchable(),
                Tables\Columns\IconColumn::make('is_completed')
                    ->boolean()
                    ->label('Done'),
                Tables\Columns\ImageColumn::make('evidence_path')->label('Evidence')->circular(),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->limit(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
