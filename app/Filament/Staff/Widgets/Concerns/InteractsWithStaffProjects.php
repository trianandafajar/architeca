<?php

namespace App\Filament\Staff\Widgets\Concerns;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithStaffProjects
{
    protected function assignedProjectsQuery(): Builder
    {
        return auth()->user()->projects()->getQuery();
    }

    /** @return array<int, int> */
    protected function assignedProjectIds(): array
    {
        return $this->assignedProjectsQuery()->pluck('projects.id')->all();
    }
}
