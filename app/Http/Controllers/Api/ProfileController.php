<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(Request $request): UserResource
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['sometimes', 'required', 'confirmed', Password::min(8)],
        ]);
        unset($data['current_password']);

        $user->update($data);

        return new UserResource($user->refresh());
    }

    public function updateAvatar(Request $request): UserResource
    {
        $data = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $user = $request->user();
        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
        }
        $user->forceFill(['avatar_url' => $data['avatar']->store('avatars', 'public')])->save();

        return new UserResource($user->refresh());
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
            $user->forceFill(['avatar_url' => null])->save();
        }

        return response()->noContent();
    }
}
