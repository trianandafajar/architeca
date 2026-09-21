<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers\Concerns;

use App\Models\User;
use Filament\Forms\Get;

trait FiltersProjectMemberUserOptions
{
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
