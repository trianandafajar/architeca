<div class="fixed inset-0 flex min-h-screen w-full overflow-y-auto font-sans" x-data="{ showPassword: false }">
    <div
        class="hidden min-h-screen lg:flex lg:w-1/2 lg:shrink-0 flex-col justify-between bg-[#F97316] px-12 py-12 border-e border-[#FED7AA]">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center rounded-xl">
                <img src="{{ asset('images/logo.png') }}" alt="Architeca Logo" class="h-12 w-12" />
            </div>
            <span class="text-2xl font-bold text-white">ARCHITECA</span>
        </div>

        <div>
            <h1 class="mb-4 text-white" style="font-size: 2.75rem; font-weight: 800; line-height: 1.15;">
                Contractor
                Management System
            </h1>
            <p class="mb-10 max-w-sm text-white">
                Manage your projects, track budgets, and run your construction business seamlessly from one place.
            </p>
        </div>

        <p class="text-sm text-white">&copy; {{ date('Y') }} Architeca. All rights reserved.</p>
    </div>

    <div
        class="flex min-h-screen w-full lg:w-1/2 lg:shrink-0 flex-col justify-center bg-[#FFFBF7] px-6 py-10 sm:px-10 sm:py-14 lg:px-16 lg:py-16">
        <div
            class="mx-auto w-full max-w-md rounded-2xl border border-[#E5DDD3] bg-white p-6 shadow-lg sm:p-8 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <div class="mb-8 flex items-center gap-2 lg:hidden">
                <div class="flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Architeca Logo" class="h-10 w-10" />
                </div>
                <span class="text-xl font-bold text-[#7c2d12]">ARCHITECA</span>
            </div>
            <h1 class="mb-2 text-2xl font-bold text-[#7c2d12] lg:text-[#7c2d12]">Welcome back</h1>
            <p class="mb-8 text-sm text-white/70 lg:text-white/70">Enter your credentials to access your account.
            </p>

            <form wire:submit="authenticate" class="space-y-6">
                @csrf

                <div>
                    <label for="data.email" class="mb-2 block text-sm font-medium text-[#7c2d12]">Email
                        address</label>
                    <div style="position: relative;">
                        <x-heroicon-o-envelope class="h-5 w-5 text-[#B8A98F]"
                            style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" />
                        <input type="email" id="data.email" wire:model="data.email" placeholder="name@example.com"
                            required
                            class="w-full rounded-xl border border-[#fed7aa] bg-white py-3 text-sm text-[#7c2d12] placeholder-[#fdba74] shadow-sm transition focus:border-[#F97316] focus:outline-none focus:ring-2 focus:ring-[#F97316]/30"
                            style="padding-left: 44px; padding-right: 16px;" />
                    </div>
                    @error('data.email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label for="data.password" class="block text-sm font-medium text-[#7c2d12]">Password</label>
                        <a href="{{ filament()->getRequestPasswordResetUrl() }}"
                            class="text-sm font-medium text-[#F97316] hover:underline">Forgot password?</a>
                    </div>
                    <div style="position: relative;">
                        <x-heroicon-o-lock-closed class="h-5 w-5 text-[#fdba74]"
                            style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" />
                        <input :type="showPassword ? 'text' : 'password'" id="data.password" wire:model="data.password"
                            placeholder="Enter your password" required
                            class="w-full rounded-xl border border-[#fed7aa] bg-white py-3 text-sm text-[#7c2d12] placeholder-[#fdba74] shadow-sm transition focus:border-[#F97316] focus:outline-none focus:ring-2 focus:ring-[#F97316]/30"
                            style="padding-left: 44px; padding-right: 44px;" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="text-[#fdba74] hover:text-[#F97316]"
                            style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: transparent; border: 0;">
                            <x-heroicon-o-eye x-show="!showPassword" class="h-5 w-5" />
                            <x-heroicon-o-eye-slash x-show="showPassword" class="h-5 w-5" x-cloak />
                        </button>
                    </div>
                    @error('data.password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="data.remember"
                        class="h-4 w-4 rounded border-[#fed7aa] text-[#F97316] focus:ring-[#F97316]/30" />
                    <span class="text-sm text-[#7c2d12]">Remember me</span>
                </label>

                <button type="submit"
                    class="w-full rounded-xl bg-[#F97316] px-6 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-[#EA580C] focus:outline-none focus:ring-2 focus:ring-[#F97316]/50 focus:ring-offset-2 active:scale-[0.98]"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <span wire:loading.remove>Sign In</span>
                    <span wire:loading wire:target="authenticate">Signing in...</span>
                </button>
            </form>
        </div>
    </div>
</div>
</div>