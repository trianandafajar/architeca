<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(
    'project_id',
    'user_id',
    'report_date',
    'workers_count',
    'work_description',
    'issues',
    'progress_percentage',
)]
class DailyReport extends Model
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'workers_count' => 'integer',
            'progress_percentage' => 'decimal:2',
        ];
    }
}
