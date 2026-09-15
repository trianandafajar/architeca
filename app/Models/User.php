<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasPanelShield;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
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
