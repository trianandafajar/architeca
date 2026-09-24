<?php

namespace App\Filament\Contractor\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ProjectResource\RelationManagers\ExpensesRelationManager as BaseExpensesRelationManager;

class ExpensesRelationManager extends BaseExpensesRelationManager
{
    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return false;
    }
}
