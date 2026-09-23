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
                ? 'translate-x-0 rtl:-translate-x-0 lg:sticky'
                : '-translate-x-full rtl:translate-x-full lg:sticky lg:translate-x-0 rtl:lg:-translate-x-0'
        " @else @if ($hasTopNav) x-cloak
    x-bind:class="$store.sidebar.isOpen ? 'translate-x-0 rtl:-translate-x-0' : '-translate-x-full rtl:translate-x-full'"
    @else x-cloak="-lg" x-bind:class="
                $store.sidebar.isOpen ? 'translate-x-0 rtl:-translate-x-0 lg:sticky' : 'w-[--sidebar-width] -translate-x-full rtl:translate-x-full lg:sticky'
            " @endif @endif x-bind:style="
        @if ($collapsibleOnDesktop || $fullyCollapsible)
            $store.sidebar.isOpen ? 'width: 16rem' : 'width: 4rem'
        @else
            'width: 16rem'
        @endif
    " {{ $attributes->class([
    'fi-sidebar fixed inset-y-0 start-0 z-[70] flex h-screen shrink-0 flex-col overflow-hidden transition-all
    duration-300 bg-[#fffbeb]
    lg:z-0',
    ])
    }}
    >
    {{-- header sidebar --}}
    <div class="flex h-16 shrink-0 items-center border-b border-white/10 px-3 transition-all duration-300"
        @if ($collapsibleOnDesktop || $fullyCollapsible)
        x-bind:class="$store.sidebar.isOpen ? 'justify-start gap-3' : 'justify-center gap-0'"
        @else
        class="justify-start gap-3"
        @endif>
        @if ($homeUrl = filament()->getHomeUrl())
        <a {{ \Filament\Support\generate_href_html($homeUrl) }}
            class="flex min-w-0 items-center gap-2.5 font-bold text-white">
            @else
            <div class="flex min-w-0 items-center gap-2.5 font-bold text-white">
                @endif
                <div class="flex h-9 w-9 shrink-0 items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}?v=2" alt="Architeca Logo" class="h-9 w-9 object-contain" />
                </div>
                @if ($collapsibleOnDesktop || $fullyCollapsible)
                <span class="text-2xl tracking-tight whitespace-nowrap" x-show="$store.sidebar.isOpen"
                    x-transition.opacity>
                    Architeca
                </span>
                @else
                <span class="text-xl tracking-tight whitespace-nowrap">
                    Architeca
                </span>
                @endif
                @if ($homeUrl)
        </a>
        @else
    </div>
    @endif
    </div>

    {{-- navigasi --}}
    <nav class="flex flex-1 flex-col overflow-y-auto overflow-x-hidden px-2 py-2 space-y-6">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_START) }}

        @foreach ($navigation as $group)
        <div>
            <ul class="space-y-2" @if ($collapsibleOnDesktop || $fullyCollapsible)
                x-bind:class="$store.sidebar.isOpen ? 'px-2' : 'px-0'"
                @else
                class="space-y-2 px-2"
                @endif>
                @foreach ($group->getItems() as $item)
                <li>
                    <a href="{{ $item->getUrl() }}" @if ($collapsibleOnDesktop || $fullyCollapsible)
                        x-data="{ tooltip: false }" x-effect="
                            tooltip = $store.sidebar.isOpen
                                ? false
                                : { content: @js($item->getLabel()), placement: 'right', theme: $store.theme }
                        " x-tooltip.html="tooltip" @endif
                        @class([ 'flex items-center gap-3 rounded-lg h-11 text-sm font-medium transition-colors'
                        , 'bg-primary text-white'=> $item->isActive(),
                        'text-gray-300 hover:bg-gray-800 hover:text-white' => ! $item->isActive(),
                        ])
                        @if ($item->isActive()) aria-current="page" @endif
                        @if ($collapsibleOnDesktop || $fullyCollapsible)
                        x-bind:class="
                        $store.sidebar.isOpen
                        ? 'px-4 justify-start'
                        : 'w-11 mx-auto !px-0 justify-center'
                        "
                        x-bind:title="$store.sidebar.isOpen ? '' : @js($item->getLabel())"
                        @endif
                        >
                        @if ($icon = $item->isActive() ? ($item->getActiveIcon() ?? $item->getIcon()) :
                        $item->getIcon())
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center">
                            <x-dynamic-component :component="$icon" class="h-5 w-5 shrink-0" />
                        </span>
                        @endif
                        <span class="truncate whitespace-nowrap text-base" @if ($collapsibleOnDesktop ||
                            $fullyCollapsible) x-show="$store.sidebar.isOpen" x-transition.opacity @endif>
                            {{ $item->getLabel() }}
                        </span>
                        @if ($badge = $item->getBadge())
                        <span
                            class="ml-auto inline-flex shrink-0 items-center rounded-full bg-gray-800 px-2 py-0.5 text-xs font-medium text-white"
                            @if ($collapsibleOnDesktop || $fullyCollapsible) x-show="$store.sidebar.isOpen"
                            x-transition.opacity @endif>
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
</aside>