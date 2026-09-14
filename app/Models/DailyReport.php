<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'report_date',
        'workers_count',
        'work_description',
        'issues',
        'progress_percentage',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'progress_percentage' => 'decimal:2',
        ];
    }

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
}