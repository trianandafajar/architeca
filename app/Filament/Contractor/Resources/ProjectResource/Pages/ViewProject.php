<?php

namespace App\Filament\Contractor\Resources\ProjectResource\Pages;

use App\Filament\Contractor\Resources\ProjectResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.admin.resources.project-resource.pages.view-project';

    protected static ?string $title = 'Project details';

    public function getSubheading(): ?string
    {
        return 'A summary of project progress, costs, and activities in a single view.';
    }

    protected function getAllRelationManagers(): array
    {
        return [
            \App\Filament\Contractor\Resources\ProjectResource\RelationManagers\ProgressUpdatesRelationManager::class,
            \App\Filament\Contractor\Resources\ProjectResource\RelationManagers\DailyReportsRelationManager::class,
            \App\Filament\Contractor\Resources\ProjectResource\RelationManagers\ExpensesRelationManager::class,
            \App\Filament\Contractor\Resources\ProjectResource\RelationManagers\ProjectMembersRelationManager::class,
            \App\Filament\Contractor\Resources\ProjectResource\RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Overview';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-information-circle';
    }
}
