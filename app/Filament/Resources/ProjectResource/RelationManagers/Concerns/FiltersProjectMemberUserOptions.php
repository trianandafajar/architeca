<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers\Concerns;

use App\Models\User;
use Filament\Forms\Get;

trait FiltersProjectMemberUserOptions
{
    /**
     * Return users who are not already assigned to the current project.
     *
     * The current value is kept when editing an existing member so that
     * the edit form can still display its selected user.
     */
    protected function getAvailableProjectMemberUserOptions(Get $get): array
    {
        $currentUserId = $get('user_id');

        $existingUserIds = $this->getOwnerRecord()
            ->members()
            ->pluck('user_id')
            ->reject(fn ($userId): bool => filled($currentUserId) && (string) $userId === (string) $currentUserId)
            ->values();

        return User::query()
            ->when(
                $existingUserIds->isNotEmpty(),
                fn ($query) => $query->whereNotIn('id', $existingUserIds),
            )
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
