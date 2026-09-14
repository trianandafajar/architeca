<?php

namespace App\Http\Responses\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = Filament::auth()->user();

        if ($user) {
            return redirect()->to(match ($user->role) {
                'contractor' => route('filament.contractor.pages.dashboard'),
                'staff' => route('filament.staff.pages.dashboard'),
                default => route('filament.admin.pages.dashboard'),
            });
        }

        return redirect()->intended(Filament::getUrl());
    }
}