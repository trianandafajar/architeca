<?php

namespace App\Filament\Staff\Resources\ProjectResource\RelationManagers;

class ProjectMembersRelationManager extends \App\Filament\Contractor\Resources\ProjectResource\RelationManagers\ProjectMembersRelationManager
{
    protected static ?string $title = 'Project Members';

    public function isReadOnly(): bool
    {
        return true;
    }
}
