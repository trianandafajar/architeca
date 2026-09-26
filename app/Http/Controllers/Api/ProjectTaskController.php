<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectTaskResource;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Support\Api\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectTaskController extends Controller
{
    public function index(Request $request, int $project): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('view_any_project::task'), 403);
        $record = ProjectAccess::findAvailable($request->user(), $project);
        $tasks = $record->tasks()
            ->when($request->user()->hasRole('staff'), fn($query) => $query->where('assigned_to', $request->user()->id))
            ->with('user:id,name')
            ->latest()
            ->paginate(max(1, min($request->integer('per_page', 15), 100)));

        return ProjectTaskResource::collection($tasks);
    }

    public function store(Request $request, int $project): ProjectTaskResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        $this->authorizeTaskManagement($request, $record, 'create_project::task');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'percentage_weight' => ['required', 'numeric', 'between:0,100'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        abort_unless($record->members()->where('user_id', $data['assigned_to'])->exists(), 422, 'The assignee must be a member of this project.');

        $task = $record->tasks()->create($data);

        return new ProjectTaskResource($task->load('user:id,name'));
    }

    public function bulkStore(Request $request, int $project): AnonymousResourceCollection
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        $this->authorizeTaskManagement($request, $record, 'create_project::task');
        $data = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'tasks' => ['required', 'array', 'min:1', 'max:100'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.percentage_weight' => ['required', 'numeric', 'between:0,100'],
        ]);
        abort_unless($record->members()->where('user_id', $data['assigned_to'])->exists(), 422, 'The assignee must be a member of this project.');

        $tasks = DB::transaction(fn() => collect($data['tasks'])->map(fn(array $task) => $record->tasks()->create([
            ...$task,
            'assigned_to' => $data['assigned_to'],
        ])));

        $tasks->each->load('user:id,name');

        return ProjectTaskResource::collection($tasks);
    }

    public function show(Request $request, int $task): ProjectTaskResource
    {
        abort_unless($request->user()->can('view_project::task'), 403);
        $record = $this->visibleTasks($request)->with('user:id,name')->findOrFail($task);

        return new ProjectTaskResource($record);
    }

    public function update(Request $request, int $task): ProjectTaskResource
    {
        $record = $this->visibleTasks($request)->with('project')->findOrFail($task);
        $user = $request->user();
        $isStaff = $user->hasRole('staff');

        if ($isStaff) {
            abort_unless((int) $record->assigned_to === (int) $user->id, 403);
            $data = $request->validate([
                'is_completed' => ['sometimes', 'boolean'],
                'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
                'evidence' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'evidence_files' => ['sometimes', 'array', 'max:10'],
                'evidence_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
        } else {
            $this->authorizeTaskManagement($request, $record->project, 'update_project::task');
            $data = $request->validate([
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'percentage_weight' => ['sometimes', 'required', 'numeric', 'between:0,100'],
                'assigned_to' => ['sometimes', 'required', 'integer', 'exists:users,id'],
                'is_completed' => ['sometimes', 'boolean'],
                'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
                'evidence_files' => ['sometimes', 'array', 'max:10'],
                'evidence_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
            if (isset($data['assigned_to'])) {
                abort_unless($record->project->members()->where('user_id', $data['assigned_to'])->exists(), 422, 'The assignee must be a member of this project.');
            }
        }

        unset($data['evidence'], $data['evidence_files']);

        if ($isStaff && $request->hasFile('evidence')) {
            if ($record->evidence_path) {
                Storage::disk('public')->delete($record->evidence_path);
                $record->attachments()->where('file_path', $record->evidence_path)->delete();
            }

            $path = $request->file('evidence')->store('tasks-evidence', 'public');
            $record->evidence_path = $path;
            $record->attachments()->create([
                'project_id' => $record->project_id,
                'user_id' => $user->id,
                'file_path' => $path,
                'file_type' => Storage::disk('public')->mimeType($path),
                'caption' => 'Evidence for task: ' . $record->title,
            ]);
        }

        $paths = $record->evidence_paths ?? [];
        foreach ($request->file('evidence_files', []) as $file) {
            $paths[] = $file->store('tasks-evidence', 'public');
        }
        if ($request->hasFile('evidence_files')) {
            $record->evidence_paths = $paths;
        }

        $record->fill($data)->save();

        return new ProjectTaskResource($record->refresh()->load('user:id,name'));
    }

    public function destroy(Request $request, int $task)
    {
        $record = $this->visibleTasks($request)->with('project')->findOrFail($task);
        $this->authorizeTaskManagement($request, $record->project, 'delete_project::task');

        $record->delete();

        return response()->noContent();
    }

    private function visibleTasks(Request $request)
    {
        return ProjectTask::query()
            ->whereHas('project', fn($projects) => $projects->availableTo($request->user()))
            ->when($request->user()->hasRole('staff'), fn($tasks) => $tasks->where('assigned_to', $request->user()->id));
    }

    private function authorizeTaskManagement(Request $request, Project $project, string $permission): void
    {
        abort_unless(
            ProjectAccess::canManage($request->user(), $project) && $request->user()->can($permission),
            403,
        );
    }
}
