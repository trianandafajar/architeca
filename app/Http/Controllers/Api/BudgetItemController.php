<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BudgetItemResource;
use App\Support\Api\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BudgetItemController extends Controller
{
    public function index(Request $request, int $project): AnonymousResourceCollection
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);

        return BudgetItemResource::collection($record->budgetItems()->latest()->paginate(max(1, min($request->integer('per_page', 15), 100))));
    }

    public function store(Request $request, int $project): BudgetItemResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        abort_unless(ProjectAccess::canManage($request->user(), $record), 403);
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        return new BudgetItemResource($record->budgetItems()->create($data));
    }

    public function show(Request $request, int $project, int $budgetItem): BudgetItemResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);

        return new BudgetItemResource($record->budgetItems()->findOrFail($budgetItem));
    }

    public function update(Request $request, int $project, int $budgetItem): BudgetItemResource
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        abort_unless(ProjectAccess::canManage($request->user(), $record), 403);
        $item = $record->budgetItems()->findOrFail($budgetItem);
        $data = $request->validate([
            'item_name' => ['sometimes', 'required', 'string', 'max:255'],
            'unit' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unit_price' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);
        $item->update($data);

        return new BudgetItemResource($item->refresh());
    }

    public function destroy(Request $request, int $project, int $budgetItem)
    {
        $record = ProjectAccess::findAvailable($request->user(), $project);
        abort_unless(ProjectAccess::canManage($request->user(), $record), 403);
        $record->budgetItems()->findOrFail($budgetItem)->delete();

        return response()->noContent();
    }
}
