<?php

namespace App\Filament\Contractor\Pages;

use App\Models\Project;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $view = 'filament.contractor.pages.project-detail';

    public string $tab = 'overview';

    public function mount($record): void
    {
        abort_if(! $this->canAccessRecord($record), 403);

        $this->record = $record;
    }

    protected function canAccessRecord(Model $record): bool
    {
        $user = auth()->user();

        if ($user->role === 'contractor') {
            return $record->members()->where('user_id', $user->id)->exists()
                || $record->owner_id === $user->id;
        }

        return false;
    }
}