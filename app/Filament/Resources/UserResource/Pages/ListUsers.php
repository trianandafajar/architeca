<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getSubheading(): ?string
    {
        return 'Manage application user accounts, roles, and access.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create user')
                ->color('primary')
                ->modalDescription('Add a new user.'),
        ];
    }
}
