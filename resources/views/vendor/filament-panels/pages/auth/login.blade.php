<div class="fixed inset-0 flex min-h-screen w-full overflow-y-auto font-sans" x-data="{ showPassword: false }">
    <div class="hidden min-h-screen lg:flex lg:w-1/2 lg:shrink-0 flex-col justify-between bg-[#2D1810] px-12 py-12">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center rounded-xl">
                <img src="{{ asset('images/logo.png') }}" alt="Architeca Logo" class="h-12 w-12" />
            </div>
            <span class="text-2xl font-bold text-[#F5E6D3]">ARCHITECA</span>
        </div>
        <div>
            <h1 class="mb-4 text-[#F5E6D3]" style="font-size: 2.75rem; font-weight: 800; line-height: 1.15;">
                Contractor<br>Management System
            </h1>
            <p class="mb-10 max-w-sm text-[#C9B99A]">
                Manage your projects, track budgets, and run your construction business seamlessly from one place.
            </p>
        </div>
        <p class="text-sm text-[#8B7355]">&copy; {{ date('Y') }} Architeca. All rights reserved.</p>
    </div>

    <div class="flex min-h-screen w-full lg:w-1/2 lg:shrink-0 flex-col justify-center bg-[#FFFBF7] px-6 py-10 sm:px-10 sm:py-14 lg:px-16 lg:py-16">
        <div class="mx-auto w-full max-w-md rounded-2xl border border-[#E5DDD3] bg-white p-6 shadow-lg sm:p-8 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <div class="mb-8 flex items-center gap-2 lg:hidden">
                <img src="{{ asset('images/logo.png') }}" alt="Architeca Logo" class="h-10 w-10" />
                <span class="text-xl font-bold text-[#2D1810]">ARCHITECA</span>
            </div>
            <h1 class="mb-2 text-2xl font-bold text-[#2D1810]">Welcome back</h1>
            <p class="mb-8 text-sm text-[#8B7355]">Enter your credentials to access your account.</p>

            <form wire:submit="authenticate" class="space-y-6">
                {{ $this->form }}

                <div class="flex items-center justify-end">
                    <a href="{{ filament()->getRequestPasswordResetUrl() }}" class="text-sm font-medium text-[#8B4513] hover:underline">Forgot password?</a>
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-[#8B4513] px-6 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-[#6B3410] focus:outline-none focus:ring-2 focus:ring-[#8B4513]/50 focus:ring-offset-2 active:scale-[0.98]"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <span wire:loading.remove>Sign In</span>
                    <span wire:loading wire:target="authenticate">Signing in...</span>
                </button>
            </form>
        </div>
    </div>
</div>
