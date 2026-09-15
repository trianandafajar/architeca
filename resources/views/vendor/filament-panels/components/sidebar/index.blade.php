@props([
'navigation',
])

@php
$isRtl = __('filament-panels::layout.direction') === 'rtl';
$collapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
$fullyCollapsible = filament()->isSidebarFullyCollapsibleOnDesktop();
$hasTopNav = filament()->hasTopNavigation();
@endphp

<aside x-data="{}" @if ($collapsibleOnDesktop || $fullyCollapsible) x-cloak x-bind:class="
            $store.sidebar.isOpen
                ? 'fi-sidebar-open lg:sticky'
                : '-translate-x-full rtl:translate-x-full lg:sticky lg:translate-x-0 rtl:lg:-translate-x-0'
        " @else @if ($hasTopNav) x-cloak
    x-bind:class="$store.sidebar.isOpen ? 'fi-sidebar-open' : '-translate-x-full rtl:translate-x-full'" @else
    x-cloak="-lg" x-bind:class="
                $store.sidebar.isOpen ? 'fi-sidebar-open lg:sticky' : '-translate-x-full rtl:translate-x-full lg:sticky lg:translate-x-0'
            " @endif @endif {{ $attributes->class([
    'fi-sidebar sticky top-0 z-30 flex h-screen shrink-0 flex-col overflow-hidden transition-all lg:z-0',
    ])
    }}
    >
    {{-- header sidebar --}}
    <div class="flex h-16 shrink-0 items-center gap-2 px-4">
        @if ($homeUrl = filament()->getHomeUrl())
        <a {{ \Filament\Support\generate_href_html($homeUrl) }}
            class="flex min-w-0 items-center gap-2.5 font-bold text-[#f5e6d3]">
            @else
            <div class="flex min-w-0 items-center gap-2.5 font-bold text-[#f5e6d3]">
                @endif
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#c0875a] text-white shadow-md">
                    <x-heroicon-o-building-office-2 class="h-5 w-5" />
                </div>
                <span class="truncate text-lg tracking-tight" x-show="$store.sidebar.isOpen" x-transition.opacity>
                    Architeca
                </span>
                @if ($homeUrl)
        </a>
        @else
    </div>
    @endif

    @if ($collapsibleOnDesktop || $fullyCollapsible)
    <button type="button" x-cloak x-on:click="$store.sidebar.close()" x-show="$store.sidebar.isOpen"
        class="ms-auto hidden h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[#f5e6d3]/70 transition hover:bg-[#8b4513]/30 hover:text-white lg:flex">
        <x-heroicon-o-chevron-left class="h-5 w-5" />
    </button>
    @endif
    </div>

    {{-- navigasi --}}
    <nav class="flex flex-1 flex-col overflow-y-auto overflow-x-hidden px-2 py-4 space-y-1"
        style="scrollbar-gutter: stable">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_START) }}

        @foreach ($navigation as $group)
        <div class="mb-3">
            @if ($group->getLabel())
            <h4 x-show="$store.sidebar.isOpen"
                class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-[#c9b99a]">
                {{ $group->getLabel() }}
            </h4>
            @endif
            <ul class="space-y-1">
                @foreach ($group->getItems() as $item)
                <li>
                    <a href="{{ $item->getUrl() }}"
                        @class([ 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors'
                        , 'bg-[#8b4513] text-white shadow-sm'=> $item->isActive(),
                        'text-[#f5e6d3] hover:bg-[#8b4513]/20 hover:text-white' => ! $item->isActive(),
                        ])
                        @if ($item->isActive()) aria-current="page" @endif
                        @if ($collapsibleOnDesktop || $fullyCollapsible)
                        x-data="{ tooltip: false }"
                        @endif
                        >
                        @if ($icon = $item->isActive() ? ($item->getActiveIcon() ?? $item->getIcon()) :
                        $item->getIcon())
                        <x-dynamic-component :component="$icon" class="h-5 w-5 shrink-0" />
                        @endif
                        <span class="truncate" x-show="$store.sidebar.isOpen" x-transition.opacity>
                            {{ $item->getLabel() }}
                        </span>
                        @if ($badge = $item->getBadge())
                        <span x-show="$store.sidebar.isOpen"
                            class="ml-auto inline-flex shrink-0 items-center rounded-full bg-[#c0875a]/30 px-2 py-0.5 text-xs font-medium text-[#f5e6d3]">
                            {{ $badge }}
                        </span>
                        @endif
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endforeach

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_END) }}
    </nav>

    {{-- footer --}}
    <div class="shrink-0 border-t border-[#462012] p-3">
        <div class="flex items-center gap-3 rounded-lg px-2 py-2">
            @if ($user = auth()->user())
            <div
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#8b4513] text-sm font-semibold text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1" x-show="$store.sidebar.isOpen" x-transition.opacity>
                <p class="truncate text-sm font-medium text-[#f5e6d3]">{{ $user->name }}</p>
                <p class="truncate text-xs text-[#c9b99a]">{{ $user->role }}</p>
            </div>
            @endif
        </div>
    </div>
</aside>