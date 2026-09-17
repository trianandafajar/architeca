<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'attachable_type',
        'attachable_id',
        'file_path',
        'file_type',
        'caption',
    ];

    protected static function booted(): void
    {
        static::creating(function (Attachment $attachment) {
            if (empty($attachment->user_id) && auth()->check()) {
                $attachment->user_id = auth()->id();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailyReport()
    {
        return $this->morphTo(DailyReport::class, 'attachable');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
