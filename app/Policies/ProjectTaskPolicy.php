<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ProjectTask;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectTaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProjectTask $projectTask): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array(strtolower($user->role), ['admin', 'contractor']);
    }

    public function update(User $user, ProjectTask $projectTask): bool
    {
        return true;
    }

    public function delete(User $user, ProjectTask $projectTask): bool
    {
        return in_array(strtolower($user->role), ['admin', 'contractor']);
    }
}
