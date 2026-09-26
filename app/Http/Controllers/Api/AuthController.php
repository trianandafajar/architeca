<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Authenticate a user and issue a 30-day bearer token. */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        $token = $user->createToken('architeca-api', ['*'], now()->addDays(30));

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->accessToken->expires_at?->toISOString(),
            ],
        ]);
    }

    /** Revoke the bearer token used for this request. */
    public function logout(): JsonResponse
    {
        request()->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Token revoked successfully.']);
    }

    /** Return the authenticated user's profile. */
    public function user(): UserResource
    {
        return new UserResource(request()->user());
    }
}
