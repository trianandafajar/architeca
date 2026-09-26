<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Support\Api\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        abort_unless($user->hasRole('staff') ? $user->can('view_any_project::task') : $user->can('view_any_project'), 403);
        $projects = Project::query()->availableTo($user)->withCount(['tasks', 'members'])
            ->latest()->paginate(max(1, min($request->integer('per_page', 15), 100)));

        return ProjectResource::collection($projects);
    }

    public function store(Request $request): ProjectResource
    {
        abort_unless($request->user()->can('create_project'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['sometimes', 'in:planning,active,on_hold,completed,cancelled'],
            'budget_items' => ['sometimes', 'array', 'max:100'],
            'budget_items.*.item_name' => ['required', 'string', 'max:255'],
            'budget_items.*.unit' => ['required', 'numeric', 'min:0'],
            'budget_items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'members' => ['required', 'array', 'min:1', 'max:100'],
            'members.*.user_id' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'members.*.role' => ['required', Rule::in(['supervisor', 'worker'])],
        ]);

        $budgetItems = $data['budget_items'] ?? [];
        $members = $data['members'];
        unset($data['budget_items'], $data['members']);

        $project = DB::transaction(function () use ($request, $data, $budgetItems, $members) {
            $project = Project::query()->create([...$data, 'owner_id' => $request->user()->id]);
            $project->budgetItems()->createMany($budgetItems);
            $project->members()->createMany($members);

            return $project;
        });

        return new ProjectResource($project->loadCount(['tasks', 'members']));
    }

    public function show(Request $request, int $project): ProjectResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project)->loadCount(['tasks', 'members']);

        return new ProjectResource($record);
    }

    public function update(Request $request, int $project): ProjectResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        abort_unless(ProjectAccess::canManage($request->user(), $record), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'client_name' => ['sometimes', 'required', 'string', 'max:255'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'contract_value' => ['sometimes', 'required', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'in:planning,active,on_hold,completed,cancelled'],
        ]);

        if (isset($data['start_date']) || isset($data['end_date'])) {
            $startDate = Carbon::parse($data['start_date'] ?? $record->start_date);
            $endDate = Carbon::parse($data['end_date'] ?? $record->end_date);
            abort_unless($endDate->isAfter($startDate), 422, 'End date must be after the start date.');
        }

        $record->update($data);

        return new ProjectResource($record->loadCount(['tasks', 'members']));
    }

    public function destroy(Request $request, int $project)
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        abort_unless($request->user()->can('delete_project') && ProjectAccess::canManage($request->user(), $record), 403);
        $record->delete();

        return response()->noContent();
    }
}
