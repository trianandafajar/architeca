<?php

namespace App\Filament\Staff\Widgets\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait InteractsWithStaffProjects
{
    protected function assignedProjectsQuery(): Builder
    {
        return auth()->user()->projects()->getQuery();
    }

    protected function assignedProjectIds(): array
    {
        return $this->assignedProjectsQuery()->pluck('projects.id')->all();
    }
}
