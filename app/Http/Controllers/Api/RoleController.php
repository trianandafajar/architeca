<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('view_any_role'), 403);
        $model = config('permission.models.role');
        $roles = $model::query()->whereIn('name', ['admin', 'contractor', 'staff'])
            ->with('permissions')->orderBy('name')->get();

        return response()->json(['data' => $roles->map(fn ($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'permissions' => $role->permissions->pluck('name')->values(),
        ])]);
    }

    public function show(Request $request, string $role)
    {
        abort_unless($request->user()->can('view_role'), 403);
        $model = config('permission.models.role');
        $record = $model::query()->whereIn('name', ['admin', 'contractor', 'staff'])
            ->with('permissions')->where('name', $role)->firstOrFail();

        return response()->json(['data' => [
            'id' => $record->id,
            'name' => $record->name,
            'guard_name' => $record->guard_name,
            'permissions' => $record->permissions->pluck('name')->values(),
        ]]);
    }

    public function update(Request $request, string $role)
    {
        abort_unless($request->user()->can('update_role'), 403);
        $model = config('permission.models.role');
        $record = $model::query()->whereIn('name', ['admin', 'contractor', 'staff'])
            ->where('name', $role)->firstOrFail();
        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);
        $record->syncPermissions($data['permissions']);
        $record->load('permissions');

        return response()->json(['data' => [
            'id' => $record->id,
            'name' => $record->name,
            'guard_name' => $record->guard_name,
            'permissions' => $record->permissions->pluck('name')->values(),
        ]]);
    }
}
