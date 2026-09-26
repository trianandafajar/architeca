<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'owner_id',
    'name',
    'client_name',
    'location',
    'contract_value',
    'start_date',
    'end_date',
    'status',
])]
class Project extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'client_name',
        'location',
        'contract_value',
        'start_date',
        'end_date',
        'status',
    ];

    use HasFactory;

    protected function casts(): array
    {
        return [
            'contract_value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function scopeAvailableTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return $query;
        }

        if ($user->hasRole('contractor')) {
            return $query->where(function (Builder $projects) use ($user): void {
                $projects->where('owner_id', $user->id)
                    ->orWhereHas('members', fn (Builder $members) => $members->where('user_id', $user->id));
            });
        }

        if ($user->hasRole('staff')) {
            return $query->whereHas('members', fn (Builder $members) => $members->where('user_id', $user->id));
        }

        return $query->whereRaw('1 = 0');
    }

    public function getProgressAttribute(): float
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $this->tasks()->where('is_completed', true)->count();

        return ($completed / $total) * 100;
    }
}
