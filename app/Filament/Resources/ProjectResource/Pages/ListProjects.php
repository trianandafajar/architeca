<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    public function getSubheading(): ?string
    {
        return 'Manage project information, progress, costs, and members in one place.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create project')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->modalDescription('Tambahkan project baru dengan informasi dasar, nilai kontrak, dan periode pelaksanaannya.')
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
