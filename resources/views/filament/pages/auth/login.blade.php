<x-filament-panels::layout.base :livewire="$this">
    <div class="flex min-h-screen font-sans">

        {{-- Left Panel: Branding --}}
        <div class="hidden w-1/2 flex-col items-center justify-center bg-[#2D1810] px-12 text-center lg:flex">
            <div class="mb-8 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#C0875A]">
                    <x-heroicon-o-building-office-2 class="h-7 w-7 text-white" />
                </div>
                <span class="text-2xl font-bold text-[#F5E6D3]">ARCHITECA</span>
            </div>

            <p class="mb-12 max-w-sm text-lg text-[#C9B99A]">
                Contractor Website
                Management System
            </p>

            <ul class="mb-12 space-y-4 text-left">
                <li class="flex items-center gap-3 text-[#F5E6D3]">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#C0875A]/20">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-[#C0875A]" />
                    </div>
                    <span>Project Management</span>
                </li>
                <li class="flex items-center gap-3 text-[#F5E6D3]">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#C0875A]/20">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-[#C0875A]" />
                    </div>
                    <span>Budget Tracking</span>
                </li>
                <li class="flex items-center gap-3 text-[#F5E6D3]">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#C0875A]/20">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-[#C0875A]" />
                    </div>
                    <span>Daily Reports</span>
                </li>
                <li class="flex items-center gap-3 text-[#F5E6D3]">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#C0875A]/20">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-[#C0875A]" />
                    </div>
                    <span>Team Collaboration</span>
                </li>
            </ul>

            <p class="text-sm text-[#8B7355]">&copy; {{ date('Y') }} Architeca. All rights reserved.</p>
        </div>

        {{-- Right Panel: Login Form --}}
        <div class="flex w-full flex-col items-center justify-center bg-[#FFFBF7] px-6 lg:w-1/2">
            <div class="w-full max-w-md">
                {{-- Mobile Logo --}}
                <div class="mb-8 flex items-center justify-center gap-2 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#8B4513]">
                        <x-heroicon-o-building-office-2 class="h-6 w-6 text-white" />
                    </div>
                    <span class="text-xl font-bold text-[#2D1810]">ARCHITECA</span>
                </div>

                <h1 class="mb-2 text-2xl font-bold text-[#2D1810]">Selamat Datang Kembali</h1>
                <p class="mb-8 text-sm text-[#8B7355]">Masukkan kredensial Anda untuk mengakses akun.</p>

                <form wire:submit="authenticate" class="space-y-5">
                    @csrf

                    <div>
                        <label for="data.email" class="mb-1.5 block text-sm font-medium text-[#2D1810]">Email</label>
                        <input
                            type="email"
                            id="data.email"
                            wire:model="data.email"
                            placeholder="nama@email.com"
                            required
                            class="w-full rounded-xl border border-[#E5DDD3] bg-white px-4 py-3 text-sm text-[#2D1810] placeholder-[#B8A98F] shadow-sm transition focus:border-[#8B4513] focus:outline-none focus:ring-2 focus:ring-[#8B4513]/30"
                            @error('data.email') class="border-red-400 focus:border-red-500 focus:ring-red-500/30" @enderror
                        />
                        @error('data.email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="data.password" class="mb-1.5 block text-sm font-medium text-[#2D1810]">Password</label>
                        <input
                            type="password"
                            id="data.password"
                            wire:model="data.password"
                            placeholder="Masukkan kata sandi"
                            required
                            class="w-full rounded-xl border border-[#E5DDD3] bg-white px-4 py-3 text-sm text-[#2D1810] placeholder-[#B8A98F] shadow-sm transition focus:border-[#8B4513] focus:outline-none focus:ring-2 focus:ring-[#8B4513]/30"
                            @error('data.password') class="border-red-400 focus:border-red-500 focus:ring-red-500/30" @enderror
                        />
                        @error('data.password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                wire:model="data.remember"
                                class="h-4 w-4 rounded border-[#E5DDD3] text-[#8B4513] focus:ring-[#8B4513]/30"
                            />
                            <span class="text-sm text-[#8B7355]">Catat Saya</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-[#8B4513] px-6 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-[#6B3410] focus:outline-none focus:ring-2 focus:ring-[#8B4513]/50 focus:ring-offset-2 active:scale-[0.98]"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-wait"
                    >
                        <span wire:loading.remove>MASUK</span>
                        <span wire:loading wire:target="authenticate">Memproses...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::layout.base>