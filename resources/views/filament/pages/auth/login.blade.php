<div class="fixed inset-0 flex min-h-screen w-screen font-sans" x-data="{ showPassword: false }">
    <div class="hidden w-1/2 flex-col justify-between bg-[#2D1810] px-12 py-12 lg:flex">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#C0875A]">
                <x-heroicon-o-building-office-2 class="h-7 w-7 text-white" />
            </div>
            <span class="text-2xl font-bold text-[#F5E6D3]">ARCHITECA</span>
        </div>

        <div>
            <h1 class="mb-4 text-[#F5E6D3]" style="font-size: 2.75rem; font-weight: 800; line-height: 1.15;">
                Contractor
                Management System
            </h1>
            <p class="mb-10 max-w-sm text-[#C9B99A]">
                Manage your projects, track budgets, and run your construction business seamlessly from one place.
            </p>
        </div>

        <p class="text-sm text-[#8B7355]">&copy; {{ date('Y') }} Architeca. All rights reserved.</p>
    </div>

    <div class="flex w-1/2 flex-col justify-center bg-[#FFFBF7] px-16 py-16">
        <div class="mx-auto w-full max-w-md">
            {{-- <div class="mb-8 flex items-center gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#8B4513]">
                    <x-heroicon-o-building-office-2 class="h-6 w-6 text-white" />
                </div>
                <span class="text-xl font-bold text-[#2D1810]">ARCHITECA</span>
            </div> --}}

            <h1 class="mb-2 text-2xl font-bold text-[#2D1810]">Welcome back</h1>
            <p class="mb-8 text-sm text-[#8B7355]">Enter your credentials to access your account.</p>

            <form wire:submit="authenticate" class="space-y-6">
                @csrf

                <div>
                    <label for="data.email" class="mb-2 block text-sm font-medium text-[#2D1810]">Email address</label>
                    <div style="position: relative;">
                        <x-heroicon-o-envelope class="h-5 w-5 text-[#B8A98F]"
                            style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" />
                        <input type="email" id="data.email" wire:model="data.email" placeholder="name@example.com"
                            required
                            class="w-full rounded-xl border border-[#E5DDD3] bg-white py-3 text-sm text-[#2D1810] placeholder-[#B8A98F] shadow-sm transition focus:border-[#8B4513] focus:outline-none focus:ring-2 focus:ring-[#8B4513]/30"
                            style="padding-left: 44px; padding-right: 16px;" />
                    </div>
                    @error('data.email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label for="data.password" class="block text-sm font-medium text-[#2D1810]">Password</label>
                        <a href="#" class="text-sm font-medium text-[#8B4513] hover:underline">Forgot password?</a>
                    </div>
                    <div style="position: relative;">
                        <x-heroicon-o-lock-closed class="h-5 w-5 text-[#B8A98F]"
                            style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" />
                        <input :type="showPassword ? 'text' : 'password'" id="data.password" wire:model="data.password"
                            placeholder="Enter your password" required
                            class="w-full rounded-xl border border-[#E5DDD3] bg-white py-3 text-sm text-[#2D1810] placeholder-[#B8A98F] shadow-sm transition focus:border-[#8B4513] focus:outline-none focus:ring-2 focus:ring-[#8B4513]/30"
                            style="padding-left: 44px; padding-right: 44px;" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="text-[#B8A98F] hover:text-[#8B4513]"
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
                        class="h-4 w-4 rounded border-[#E5DDD3] text-[#8B4513] focus:ring-[#8B4513]/30" />
                    <span class="text-sm text-[#8B7355]">Remember me</span>
                </label>

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