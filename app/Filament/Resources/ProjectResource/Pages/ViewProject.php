<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ProjectResource\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\BudgetItemsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ExpensesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ProjectMembersRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.admin.resources.project-resource.pages.view-project';

    protected static ?string $title = 'Project details';

    protected function getAllRelationManagers(): array
    {
        return [
            TasksRelationManager::class,
            BudgetItemsRelationManager::class,
            ExpensesRelationManager::class,
            ProjectMembersRelationManager::class,
            AttachmentsRelationManager::class,
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
