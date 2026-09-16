<?php

namespace App\Filament\Contractor\Resources\ProjectResource\Pages;

use App\Filament\Contractor\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    public function getSubheading(): ?string
    {
        return 'Kelola project yang Anda miliki atau yang menugaskan Anda sebagai anggota.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create project')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->modalDescription('Tambahkan project baru untuk dikelola bersama tim.')
                ->modalFooterActionsAlignment(Alignment::End)
                ->extraModalWindowAttributes(['class' => 'architeca-project-modal'])
                ->extraAttributes(['class' => 'architeca-create-project-action']),
        ];
    }
}
