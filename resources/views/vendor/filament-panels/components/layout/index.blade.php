@php
use Filament\Support\Enums\MaxWidth;

$navigation = filament()->getNavigation();
$livewire ??= null;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">

    {{-- overlay --}}
    @if (filament()->hasNavigation())
    <div x-cloak x-data="{}" x-on:click="$store.sidebar.close()" x-show="$store.sidebar.isOpen"
        x-transition.opacity.300ms class="fixed inset-0 z-30 bg-black/50 transition duration-300 lg:hidden"></div>
    @endif

    <div class="flex min-h-screen w-full">
        {{-- sidebar --}}
        @if (filament()->hasNavigation())
        <x-filament-panels::sidebar :navigation="$navigation" class="fi-main-sidebar shrink-0" />
        @endif

        {{-- main --}}
        <div class="fi-main-ctn flex min-w-0 flex-1 flex-col">
            {{-- header --}}
            @if (filament()->hasTopbar())
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_BEFORE,
            scopes: $livewire?->getRenderHookScopes()) }}

            <header
                class="z-40 flex h-16 shrink-0 items-center gap-4 border-b border-border bg-background px-6 transition-all">
                <div class="flex items-center gap-3">
                    <button x-data x-on:click="
                                @if (filament()->isSidebarCollapsibleOnDesktop() || filament()->isSidebarFullyCollapsibleOnDesktop())
                                    if (window.innerWidth >= 1024) {
                                        $store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()
                                    } else {
                                        $store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()
                                    }
                                @else
                                    $store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()
                                @endif
                            "
                        class="inline-flex items-center justify-center rounded-lg text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring h-9 w-9 text-muted-foreground">
                        <x-heroicon-o-bars-3 class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex-1"></div>

                <div class="flex items-center gap-2">
                    {{-- user menu --}}
                    <x-filament-panels::user-menu />
                </div>
            </header>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_AFTER, scopes:
            $livewire?->getRenderHookScopes()) }}
            @endif

            <main class="flex-1 overflow-auto bg-background">
                <div class="mx-auto w-full max-w-[1600px] px-4 py-8 sm:px-6 lg:px-10">
                    {{ $slot }}
                </div>
            </main>

        </div>
    </div>
</x-filament-panels::layout.base>