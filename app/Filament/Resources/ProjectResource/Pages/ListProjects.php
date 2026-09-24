<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
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
                ->modalDescription('Add a new project with basic information, the contract value, and the implementation period.')
                ->modalFooterActionsAlignment(Alignment::End)
                ->extraModalWindowAttributes([
                    'class' => 'architeca-project-modal',
                ])
                ->extraAttributes([
                    'class' => 'architeca-create-project-action',
                ]),
        ];
    }
}
