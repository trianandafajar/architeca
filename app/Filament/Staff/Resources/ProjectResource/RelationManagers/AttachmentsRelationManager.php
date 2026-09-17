<?php

namespace App\Filament\Staff\Resources\ProjectResource\RelationManagers;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Contractor\Resources\ProjectResource\RelationManagers\AttachmentsRelationManager as ContractorAttachmentsRelationManager;
use Illuminate\Support\Facades\Auth;

class AttachmentsRelationManager extends ContractorAttachmentsRelationManager
{
    protected static ?string $title = 'Attachments';

    protected static ?string $icon = 'heroicon-o-paper-clip';

    public function table(Table $table): Table
    {
        return parent::table($table)->modifyQueryUsing(fn (Builder $query): Builder => $query->where('user_id', Auth::id()));
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
