<?php

namespace App\Filament\Contractor\Widgets\Concerns;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithContractorProjects
{
    protected function accessibleProjectsQuery(): Builder
    {
        $userId = auth()->id();

        return Project::query()->where(function (Builder $query) use ($userId): void {
            $query
                ->where('owner_id', $userId)
                ->orWhereHas('members', fn (Builder $members) => $members->where('user_id', $userId));
        });
    }

    /** @return array<int, int> */
    protected function accessibleProjectIds(): array
    {
        return $this->accessibleProjectsQuery()->pluck('id')->all();
    }
}
