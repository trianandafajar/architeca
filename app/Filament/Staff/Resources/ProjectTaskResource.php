<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\ProjectTaskResource\Pages;
use App\Filament\Staff\Resources\ProjectTaskResource\RelationManagers;
use App\Models\ProjectTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectTaskResource extends Resource
{
    protected static ?string $model = ProjectTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'My Tasks';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('assigned_to', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Task Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Task Title')
                            ->required(),
                        Forms\Components\TextInput::make('percentage_weight')
                            ->label('Weight (%)')
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('assigned_to')
                            ->relationship('user', 'name')
                            ->required(),
                    ]),
            ]);
    }

    public static function getEditForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Task Execution')
                    ->schema([
                        Forms\Components\Checkbox::make('is_completed')
                            ->label('Completed')
                            ->default(false),
                        Forms\Components\FileUpload::make('evidence_path')
                            ->label('Evidence Photo')
                            ->image()
                            ->directory('tasks-evidence'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes'),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectsForTasks::route('/'),
            'project-view' => Pages\ViewProjectTasks::route('/project/{project}'),
            'edit' => Pages\EditProjectTask::route('/{record}/edit'),
        ];
    }
}
