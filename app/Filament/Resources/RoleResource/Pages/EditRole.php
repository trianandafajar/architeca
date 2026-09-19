<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public function getSubheading(): ?string
    {
        return 'Edit the role and manage its associated permissions.';
    }

    public Collection $permissions;

    protected string $permissionGuardName;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->extraAttributes([
                    'class' => 'disabled:opacity-50 disabled:cursor-not-allowed',
                ]),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $permissionData = collect($data)
            ->except(['name', 'guard_name', 'select_all', Utils::getTenantModelForeignKey()]);

        // Keep the current assignments if a form submission does not contain
        // any permission fields (for example while a permission tab is not
        // mounted). This prevents an unrelated role edit from clearing all
        // permissions through syncPermissions().
        $submittedPermissions = $permissionData
            ->values()
            ->flatten()
            ->filter(fn(mixed $permission): bool => filled($permission))
            ->unique()
            ->values();

        $this->permissions = $submittedPermissions->isEmpty()
            ? $this->record->permissions()->pluck('name')
            : $submittedPermissions;

        $this->permissionGuardName = $data['guard_name']
            ?? $this->record->getAttribute('guard_name')
            ?? Utils::getFilamentAuthGuard();

        if (Arr::has($data, Utils::getTenantModelForeignKey())) {
            return Arr::only($data, ['name', 'guard_name', Utils::getTenantModelForeignKey()]);
        }

        return Arr::only($data, ['name', 'guard_name']);
    }

    protected function afterSave(): void
    {
        $permissionModels = collect();
        $this->permissions->each(function ($permission) use ($permissionModels) {
            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $this->permissionGuardName,
            ]));
        });

        $this->record->syncPermissions($permissionModels);
    }
}
