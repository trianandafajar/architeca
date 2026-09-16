<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers\Concerns;

use Filament\Actions\MountableAction;
use Filament\Support\Enums\Alignment;

trait ConfiguresProjectModalActions
{
    protected function configureProjectModalAction(MountableAction $action, string $description): MountableAction
    {
        return $action
            ->modalDescription($description)
            ->modalFooterActionsAlignment(Alignment::End)
            ->extraModalWindowAttributes([
                'class' => 'architeca-project-modal',
            ]);
    }
}
