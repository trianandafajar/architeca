<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    public function getSubheading(): ?string
    {
        return 'Manage branches across your organization.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-plus')
        ];
    }
}
