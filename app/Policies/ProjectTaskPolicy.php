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
        return $user->can('create_projecttask');
    }

    public function update(User $user, ProjectTask $projectTask): bool
    {
        return $user->can('update_projecttask');
    }

    public function delete(User $user, ProjectTask $projectTask): bool
    {
        return $user->can('delete_projecttask');
    }
}
