<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectMemberResource;
use App\Support\Api\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectMemberController extends Controller
{
    public function index(Request $request, int $project): AnonymousResourceCollection
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);

        abort_unless(ProjectAccess::canManageAsOwner($request->user(), $record), 403);

        return ProjectMemberResource::collection(
            $record->members()->with('user:id,name,email,role')->paginate(max(1, min($request->integer('per_page', 15), 100))),
        );
    }

    public function store(Request $request, int $project): ProjectMemberResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        abort_unless(ProjectAccess::canManageAsOwner($request->user(), $record), 403);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'in:supervisor,worker'],
        ]);
        abort_if($record->members()->where('user_id', $data['user_id'])->exists(), 422, 'User is already a project member.');

        $member = $record->members()->create($data);

        return new ProjectMemberResource($member->load('user:id,name,email,role'));
    }

    public function update(Request $request, int $project, int $member): ProjectMemberResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        abort_unless(ProjectAccess::canManageAsOwner($request->user(), $record), 403);
        $memberRecord = $record->members()->findOrFail($member);
        $data = $request->validate(['role' => ['required', 'in:supervisor,worker']]);

        $memberRecord->update($data);

        return new ProjectMemberResource($memberRecord->load('user:id,name,email,role'));
    }

    public function destroy(Request $request, int $project, int $member)
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        abort_unless(ProjectAccess::canManageAsOwner($request->user(), $record), 403);
        $memberRecord = $record->members()->findOrFail($member);
        abort_if((int) $record->owner_id === (int) $memberRecord->user_id, 422, 'The project owner cannot be removed.');

        $memberRecord->delete();

        return response()->noContent();
    }
}
