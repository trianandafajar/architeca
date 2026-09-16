<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()
                ->record($this->getRecord())
                ->color('primary')
                ->label('Login sebagai')
                ->redirectTo(fn (): string => $this->getRecord()->hasRole('contractor')
                    ? url('/contractor')
                    : url('/staff')),
            Actions\DeleteAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Perbarui profil, password, atau data pengguna.';
    }
}
