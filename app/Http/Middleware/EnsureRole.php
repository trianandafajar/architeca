<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $user = auth()->user();

        if ($user->role !== $role) {
            abort(403, 'Unauthorized. This panel is for ' . ucfirst($role) . ' only.');
        }

        return $next($request);
    }
}