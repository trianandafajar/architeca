<?php

namespace App\Filament\Contractor\Resources\ProjectResource\Pages;

use App\Filament\Contractor\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.admin.resources.project-resource.pages.edit-project';

    protected static ?string $title = 'Edit project';

    public function getSubheading(): ?string
    {
        return 'Update project information and manage activities from a single view.';
    }

    protected function getAllRelationManagers(): array
    {
        return [
            \App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager::class,
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

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->color('primary');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
