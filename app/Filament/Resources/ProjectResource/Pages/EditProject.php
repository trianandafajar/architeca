<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected static string $view = 'filament.admin.resources.project-resource.pages.edit-project';

    protected static ?string $title = 'Edit project';

    protected function getAllRelationManagers(): array
    {
        return [
            \App\Filament\Resources\ProjectResource\RelationManagers\TasksRelationManager::class,
            \App\Filament\Resources\ProjectResource\RelationManagers\BudgetItemsRelationManager::class,
            \App\Filament\Resources\ProjectResource\RelationManagers\ExpensesRelationManager::class,
            \App\Filament\Resources\ProjectResource\RelationManagers\ProjectMembersRelationManager::class,
            \App\Filament\Resources\ProjectResource\RelationManagers\AttachmentsRelationManager::class,
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
        return parent::getSaveFormAction()
            ->extraAttributes([
                'class' => 'disabled:opacity-50 disabled:cursor-not-allowed',
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
