<?php

namespace App\Filament\Staff\Resources\ProjectTaskResource\Pages;

use App\Filament\Staff\Resources\ProjectTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectTask extends EditRecord
{
    protected static string $resource = ProjectTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
