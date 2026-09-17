<?php

namespace App\Filament\Staff\Resources\ProjectResource\RelationManagers;

use App\Filament\Contractor\Resources\ProjectResource\RelationManagers\ProgressUpdatesRelationManager as ContractorProgressUpdatesRelationManager;

class ProgressUpdatesRelationManager extends ContractorProgressUpdatesRelationManager
{
    protected static ?string $title = 'Progress';

    protected static ?string $icon = 'heroicon-o-chart-bar';

    public function isReadOnly(): bool
    {
        return true;
    }
}
