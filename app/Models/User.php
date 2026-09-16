<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasPanelShield;

    protected static function booted(): void
    {
        static::saved(function (self $user): void {
            if (! ($user->wasRecentlyCreated || $user->wasChanged('role')) || blank($user->role)) {
                return;
            }

            $roleModel = config('permission.models.role');
            $roleModel::findOrCreate($user->role, $user->getDefaultGuardName());
            $user->syncRoles([$user->role]);
        });
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
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

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members')->withPivot('role')->withTimestamps();
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function progressUpdates()
    {
        return $this->hasMany(ProgressUpdate::class);
    }
}
