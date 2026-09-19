<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->extraAttributes([
                'class' => 'disabled:opacity-50 disabled:cursor-not-allowed',
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()
                ->record($this->getRecord())
                ->color('primary')
                ->label('Login as')
                ->requiresConfirmation()
                ->modalHeading('Confirm Login')
                ->modalDescription('Are you sure you want to log in as this user?')
                ->modalSubmitActionLabel('Yes, Login')
                ->redirectTo(fn (): string => $this->getRecord()->hasRole('contractor')
                    ? url('/contractor')
                    : url('/staff')),
            Actions\DeleteAction::make()
                ->extraAttributes([
                    'class' => 'disabled:opacity-50 disabled:cursor-not-allowed',
                ]),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Update profile, password, or user data.';
    }
}
