<?php

namespace App\Filament\Staff\Resources\ProjectResource\Pages;

use App\Filament\Staff\Resources\ProjectResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.staff.resources.project-resource.pages.view-project';
    protected static ?string $title = 'Project details';

    public function getSubheading(): ?string
    {
        return 'Ringkasan project, progres, laporan kerja, anggota, dan lampiran yang tersedia untuk Anda.';
    }

    protected function getAllRelationManagers(): array
    {
        return [
            \App\Filament\Staff\Resources\ProjectResource\RelationManagers\ProgressUpdatesRelationManager::class,
            \App\Filament\Staff\Resources\ProjectResource\RelationManagers\DailyReportsRelationManager::class,
            \App\Filament\Staff\Resources\ProjectResource\RelationManagers\ProjectMembersRelationManager::class,
            \App\Filament\Staff\Resources\ProjectResource\RelationManagers\AttachmentsRelationManager::class,
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
