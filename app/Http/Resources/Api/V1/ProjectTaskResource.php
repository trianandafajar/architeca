<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin ProjectTask */
class ProjectTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'percentage_weight' => $this->percentage_weight,
            'is_completed' => (bool) $this->is_completed,
            'notes' => $this->notes,
            'assigned_to' => $this->assigned_to,
            'assignee' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'evidence_url' => $this->evidence_path
                ? Storage::disk('public')->url($this->evidence_path)
                : null,
            'evidence_urls' => collect($this->evidence_paths ?? [])
                ->map(fn (string $path) => Storage::disk('public')->url($path))
                ->values(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
