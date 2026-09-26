<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AttachmentResource;
use App\Models\Attachment;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Support\Api\AttachmentStorage;
use App\Support\Api\ProjectAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function index(Request $request, int $project): AnonymousResourceCollection
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        $query = Attachment::query()->where('project_id', $record->id)->with('user:id,name');

        if ($request->user()->hasRole('staff')) {
            $query->where(function (Builder $attachments) use ($request): void {
                $attachments
                    ->where(function (Builder $reports) use ($request): void {
                        $reports->where('attachable_type', (new DailyReport)->getMorphClass())
                            ->whereIn('attachable_id', DailyReport::query()->where('user_id', $request->user()->id)->select('id'));
                    })
                    ->orWhere(function (Builder $tasks) use ($request): void {
                        $tasks->where('attachable_type', (new ProjectTask)->getMorphClass())
                            ->whereIn('attachable_id', ProjectTask::query()->where('assigned_to', $request->user()->id)->select('id'));
                    });
            });
        }

        return AttachmentResource::collection(
            $query->latest()->paginate(max(1, min($request->integer('per_page', 15), 100))),
        );
    }

    public function store(Request $request, int $project, AttachmentStorage $storage): AttachmentResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        $data = $request->validate([
            'target_type' => ['required', 'in:project,expense,task,daily_report,progress_update'],
            'target_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm,pdf', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $target = match ($data['target_type']) {
            'project' => Project::query()->whereKey($data['target_id'])->whereKey($record->id)->firstOrFail(),
            'expense' => $record->expenses()->findOrFail($data['target_id']),
            'task' => $record->tasks()->findOrFail($data['target_id']),
            'daily_report' => $record->dailyReports()->findOrFail($data['target_id']),
            'progress_update' => $record->progressUpdates()->findOrFail($data['target_id']),
        };

        if ($request->user()->hasRole('staff')) {
            abort_unless(
                $target instanceof DailyReport && (int) $target->user_id === (int) $request->user()->id,
                403,
            );
        } else {
            abort_unless(ProjectAccess::canManage($request->user(), $record), 403);
        }

        $attachment = $storage->attach($target, $record, $request->user(), $request->file('file'), $data['caption']);

        return new AttachmentResource($attachment->load('user:id,name'));
    }

    public function update(Request $request, int $attachment): AttachmentResource
    {
        $record = Attachment::query()->with('project')->findOrFail($attachment);
        $project = ProjectAccess::findAvailable($request->user(), $record->project_id);
        abort_unless(ProjectAccess::canManage($request->user(), $project), 403);
        $record->update($request->validate(['caption' => ['sometimes', 'nullable', 'string', 'max:255']]));

        return new AttachmentResource($record->refresh()->load('user:id,name'));
    }

    public function download(Request $request, int $attachment, AttachmentStorage $storage)
    {
        $record = Attachment::query()->with('attachable')->findOrFail($attachment);
        ProjectAccess::findAvailable($request->user(), $record->project_id);

        if ($request->user()->hasRole('staff')) {
            $allowed = ($record->attachable instanceof DailyReport && (int) $record->attachable->user_id === (int) $request->user()->id)
                || ($record->attachable instanceof ProjectTask && (int) $record->attachable->assigned_to === (int) $request->user()->id);
            abort_unless($allowed, 403);
        }

        $disk = $storage->diskFor($record);
        abort_unless($disk, 404);

        return Storage::disk($disk)->download($record->file_path, basename($record->file_path));
    }

    public function destroy(Request $request, int $attachment, AttachmentStorage $storage)
    {
        $record = Attachment::query()->with('project')->findOrFail($attachment);
        $project = ProjectAccess::findAvailable($request->user(), $record->project_id);
        abort_unless(ProjectAccess::canManage($request->user(), $project), 403);

        $storage->delete($record);

        return response()->noContent();
    }
}
