<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'project_id',
    'title',
    'percentage_weight',
    'is_completed',
    'notes',
    'assigned_to',
    'evidence_path',
    'evidence_paths',
])]
/**
 * @property int $project_id
 * @property string $title
 * @property float $percentage_weight
 * @property bool $is_completed
 * @property string|null $notes
 * @property int|null $assigned_to ID of the assigned user (must be a project member)
 * @property string|null $evidence_path
 * @property array|null $evidence_paths
 */
class ProjectTask extends Model
{
    protected $casts = [
        'evidence_paths' => 'array',
    ];
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
