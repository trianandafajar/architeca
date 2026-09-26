<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('view_any_user'), 403);

        return UserResource::collection(
            User::query()->whereKeyNot($request->user()->id)->latest()
                ->paginate(max(1, min($request->integer('per_page', 15), 100))),
        );
    }

    public function store(Request $request): UserResource
    {
        abort_unless($request->user()->can('create_user'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['contractor', 'staff'])],
        ]);
        $data['password'] = Hash::make($data['password']);

        return new UserResource(User::query()->create($data));
    }

    public function show(Request $request, int $user): UserResource
    {
        abort_unless($request->user()->can('view_user'), 403);

        return new UserResource(User::query()->whereKeyNot($request->user()->id)->findOrFail($user));
    }

    public function update(Request $request, int $user): UserResource
    {
        abort_unless($request->user()->can('update_user'), 403);
        $record = User::query()->findOrFail($user);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'password' => ['sometimes', 'required', 'confirmed', Password::min(8)],
            'role' => ['sometimes', 'required', Rule::in(['contractor', 'staff'])],
        ]);
        abort_if($record->hasAnyRole(['admin', 'super_admin']) && array_key_exists('role', $data), 422, 'System administrator roles cannot be changed here.');

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $record->update($data);

        return new UserResource($record->refresh());
    }

    public function destroy(Request $request, int $user)
    {
        abort_unless($request->user()->can('delete_user'), 403);
        $record = User::query()->findOrFail($user);
        abort_if((int) $record->id === (int) $request->user()->id || $record->hasAnyRole(['admin', 'super_admin']), 422);

        $record->delete();

        return response()->noContent();
    }
}
