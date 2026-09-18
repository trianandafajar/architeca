<?php

namespace App\Filament\Contractor\Resources\ProjectResource\Pages;

use App\Filament\Contractor\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] ??= auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public function getSubheading(): ?string
    {
        return 'Create a new project and start managing work with your team.';
    }
}
