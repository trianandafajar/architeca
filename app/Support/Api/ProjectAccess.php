<?php

namespace App\Support\Api;

use App\Models\Project;
use App\Models\User;

class ProjectAccess
{
    public static function findAvailable(User $user, int|string $projectId): Project
    {
        $project = Project::query()->availableTo($user)->findOrFail($projectId);
        $canView = $user->hasRole('staff')
            ? $user->can('view_any_project::task')
            : $user->can('view_project');

        abort_unless($canView, 403);

        return $project;
    }

    public static function canManage(User $user, Project $project): bool
    {
        if (! $user->can('update_project')) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        return $user->hasRole('contractor')
            && (int) $project->owner_id === (int) $user->id;
    }

    public static function canManageAsOwner(User $user, Project $project): bool
    {
        return self::canManage($user, $project);
    }
}
