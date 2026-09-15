<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;

class Login extends Page implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    protected static string $view = 'filament.pages.auth.login';

    protected static ?string $title = 'Masuk';

    public ?array $data = [];

    public function mount(): void
    {
        if (auth()->check()) {
            redirect()->to($this->getPanelRedirect());
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->placeholder('nama@email.com')
                    ->extraInputAttributes(['tabindex' => 1]),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    ->autocomplete('current-password')
                    ->required()
                    ->extraInputAttributes(['tabindex' => 2]),
                Checkbox::make('remember')
                    ->label('Catat Saya'),
            ])
            ->statePath('data');
    }

    public function getRedirectUrl(): string
    {
        $user = auth()->user();

        if (! $user) {
            return route('filament.admin.auth.login');
        }

        return match ($user->role) {
            'contractor' => route('filament.contractor.pages.dashboard'),
            'staff' => route('filament.staff.pages.dashboard'),
            default => route('filament.admin.pages.dashboard'),
        };
    }

    protected function getPanelRedirect(): string
    {
        $user = auth()->user();

        if (! $user) {
            return route('filament.admin.pages.dashboard');
        }

        return match ($user->role) {
            'contractor' => route('filament.contractor.pages.dashboard'),
            'staff' => route('filament.staff.pages.dashboard'),
            default => route('filament.admin.pages.dashboard'),
        };
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = auth()->user();

        if (! $user->canAccessPanel(filament()->getCurrentPanel())) {
            auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->danger();
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'Email atau kata sandi salah.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}