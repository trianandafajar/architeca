<?php

namespace App\Filament\Staff\Resources\ProjectResource\Pages;

use App\Filament\Staff\Resources\ProjectResource;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Projects';

    public function getSubheading(): ?string
    {
        return 'View projects that assign you as a member and monitor their progress.';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
