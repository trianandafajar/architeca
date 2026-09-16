<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ProjectResource\RelationManagers\Concerns\ConfiguresProjectModalActions;

class AttachmentsRelationManager extends RelationManager
{
    use ConfiguresProjectModalActions;

    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Attachments';

    protected static ?string $icon = 'heroicon-o-paper-clip';

    protected static ?string $recordTitleAttribute = 'file_path';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->disk('public')
                    ->directory('attachments')
                    ->required(),
                Forms\Components\TextInput::make('file_type')
                    ->label('Tipe File')
                    ->maxLength(100),
                Forms\Components\TextInput::make('caption')
                    ->label('Keterangan')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->limit(50),
                Tables\Columns\TextColumn::make('file_type')
                    ->label('Tipe'),
                Tables\Columns\TextColumn::make('caption')
                    ->label('Keterangan')
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                $this->configureProjectModalAction(
                    Tables\Actions\CreateAction::make()->color('primary'),
                    'Unggah file dan tambahkan keterangan untuk project ini.',
                ),
            ])
            ->actions([
                $this->configureProjectModalAction(
                    Tables\Actions\EditAction::make(),
                    'Perbarui file atau keterangan lampiran ini.',
                ),
                $this->configureProjectModalAction(
                    Tables\Actions\DeleteAction::make(),
                    'Lampiran yang dihapus tidak dapat dipulihkan.',
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    $this->configureProjectModalAction(
                        Tables\Actions\DeleteBulkAction::make(),
                        'Lampiran yang dipilih akan dihapus dan tidak dapat dipulihkan.',
                    ),
                ]),
            ]);
    }
}
