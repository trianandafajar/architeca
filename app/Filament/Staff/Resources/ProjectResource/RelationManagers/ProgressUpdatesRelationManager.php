<?php

namespace App\Filament\Staff\Resources\ProjectResource\RelationManagers;

class ProgressUpdatesRelationManager extends \App\Filament\Contractor\Resources\ProjectResource\RelationManagers\ProgressUpdatesRelationManager
{
    protected static ?string $title = 'Progress';

    protected static ?string $icon = 'heroicon-o-chart-bar';

    public function isReadOnly(): bool
    {
        return true;
    }
}
