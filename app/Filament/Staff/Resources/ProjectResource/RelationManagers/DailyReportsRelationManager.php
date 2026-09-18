<?php

namespace App\Filament\Staff\Resources\ProjectResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Contractor\Resources\ProjectResource\RelationManagers\DailyReportsRelationManager as ContractorDailyReportsRelationManager;
use Illuminate\Support\Facades\Auth;

class DailyReportsRelationManager extends ContractorDailyReportsRelationManager
{
    protected static ?string $title = 'Daily Reports';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('user_id', Auth::id()))
            ->actions([
                Tables\Actions\ViewAction::make()->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()->icon('heroicon-o-trash'),
            ])
            ->bulkActions([]);
    }

    protected function canCreate(): bool
    {
        return true;
    }

    protected function canEdit(Model $record): bool
    {
        return (int) $record->user_id === (int) Auth::id();
    }

    protected function canDelete(Model $record): bool
    {
        return (int) $record->user_id === (int) Auth::id();
    }
}
