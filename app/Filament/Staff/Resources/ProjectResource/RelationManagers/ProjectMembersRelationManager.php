<?php

namespace App\Filament\Staff\Resources\ProjectResource\RelationManagers;

use App\Filament\Contractor\Resources\ProjectResource\RelationManagers\ProjectMembersRelationManager as ContractorProjectMembersRelationManager;

class ProjectMembersRelationManager extends ContractorProjectMembersRelationManager
{
    protected static ?string $title = 'Project Members';

    public function isReadOnly(): bool
    {
        return true;
    }
}
