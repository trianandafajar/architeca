<?php

namespace App\Filament\Contractor\Resources\ProjectResource\Pages;

use App\Filament\Contractor\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create project')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->modalDescription('Add a new project to be managed with the team.')
                ->modalFooterActionsAlignment(Alignment::End)
                ->extraModalWindowAttributes(['class' => 'architeca-project-modal'])
                ->extraAttributes(['class' => 'architeca-create-project-action']),
        ];
    }
}
