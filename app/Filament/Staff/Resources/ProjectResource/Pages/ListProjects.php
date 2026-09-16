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
        return 'Lihat project yang menugaskan Anda sebagai member dan pantau progres pekerjaannya.';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
