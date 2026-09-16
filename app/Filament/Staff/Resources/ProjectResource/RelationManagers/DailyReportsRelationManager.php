<?php

namespace App\Filament\Staff\Resources\ProjectResource\RelationManagers;

use App\Models\DailyReport;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DailyReportsRelationManager extends \App\Filament\Contractor\Resources\ProjectResource\RelationManagers\DailyReportsRelationManager
{
    protected static ?string $title = 'Daily Reports';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('user_id', auth()->id()))
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    protected function canCreate(): bool
    {
        return true;
    }

    protected function canEdit(Model $record): bool
    {
        return (int) $record->user_id === (int) auth()->id();
    }

    protected function canDelete(Model $record): bool
    {
        return (int) $record->user_id === (int) auth()->id();
    }
}
