<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(
    'name',
    'email',
    'password',
    'role',
    'branch_id',
    'avatar_url',
)]
#[Hidden(
    'password',
    'remember_token',
)]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, HasRoles, HasPanelShield;

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url
            ? Storage::disk('public')->url($this->avatar_url)
            : null;
    }

    protected static function booted(): void
    {
        static::saved(function (self $user): void {
            if (
                ! ($user->wasRecentlyCreated || $user->wasChanged('role'))
                || blank($user->role)
            ) {
                return;
            }

            $roleModel = config('permission.models.role');

            $roleModel::findOrCreate(
                $user->role,
                $user->getDefaultGuardName()
            );

            $user->syncRoles([$user->role]);
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return match ($panel->getId()) {
            'admin' => $this->hasRole('admin'),
            'contractor' => $this->hasRole('contractor'),
            'staff' => $this->hasRole('staff'),
            default => false,
        };
    }

    public function canImpersonate(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    public function canBeImpersonated(): bool
    {
        return $this->hasAnyRole(['contractor', 'staff']);
    }
}
