<?php

namespace App\Filament\Staff\Resources\ProjectTaskResource\Pages;

use App\Filament\Staff\Resources\ProjectTaskResource;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\ProjectTask;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class ViewProjectTasks extends Page
{
    use WithFileUploads;

    protected static string $resource = ProjectTaskResource::class;
    protected static string $view = 'filament.staff.resources.project-task-resource.pages.view-project-tasks';

    public Project $record;
    public $tasks = [];
    public array $notes = [];
    public array $evidence_files = [];

    public function mount(int|string $project): void
    {
        $this->record = Project::whereHas('members', fn(Builder $q) => $q->where('user_id', auth()->id()))
            ->findOrFail($project);

        $this->tasks = $this->tasksQuery()->get();
        $this->notes = $this->tasks->pluck('notes', 'id')->all();
    }

    protected function tasksQuery()
    {
        return ProjectTask::where('project_id', $this->record->id)
            ->where('assigned_to', auth()->id());
    }

    protected function findTask(int $id): ProjectTask
    {
        return $this->tasksQuery()->findOrFail($id);
    }

    protected function persist(ProjectTask $task): void
    {
        if ($task->is_completed) {
            return;
        }

        if (isset($this->evidence_files[$task->id])) {
            $this->validate(["evidence_files.{$task->id}" => ['image', 'max:5120']]);

            if ($task->evidence_path) {
                Storage::disk('public')->delete($task->evidence_path);
                $task->attachments()->where('file_path', $task->evidence_path)->delete();
            }

            $path = $this->evidence_files[$task->id]->store('tasks-evidence', 'public');
            $task->evidence_path = $path;
            
            $fileType = Storage::disk('public')->mimeType($path) ?: null;

            $task->attachments()->create([
                'project_id' => $task->project_id,
                'user_id' => Auth::id(),
                'file_path' => $path,
                'file_type' => $fileType,
                'caption' => 'Evidence for task: ' . $task->title,
            ]);

            unset($this->evidence_files[$task->id]);
        }

        $task->notes = $this->notes[$task->id] ?? null;
        $task->save();

        $this->tasks = $this->tasksQuery()->get();
    }

    public function toggleTask(int $taskId): void
    {
        $task = $this->findTask($taskId);

        if (! $task->is_completed) {
            $this->persist($task);

            $pending = $this->evidence_files[$task->id] ?? null;
        }

        $task->update(['is_completed' => ! $task->is_completed]);
        $this->tasks = $this->tasksQuery()->get();
    }

    public function saveTask(int $taskId): void
    {
        $this->persist($this->findTask($taskId));

        Notification::make()->title('Saved successfully')->success()->send();
    }

    public function removeEvidence(int $taskId): void
    {
        $task = $this->findTask($taskId);
        abort_if($task->is_completed, 403);

        if ($task->evidence_path) {
            Storage::disk('public')->delete($task->evidence_path);
            $task->attachments()->where('file_path', $task->evidence_path)->delete();
        }

        $task->update(['evidence_path' => null]);
        unset($this->evidence_files[$taskId]);
        $this->tasks = $this->tasksQuery()->get();
    }
}
