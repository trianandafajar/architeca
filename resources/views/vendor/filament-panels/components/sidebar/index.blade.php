@props([
    'navigation',
])

@php
    $openSidebarClasses = 'fi-sidebar-open w-[--sidebar-width] translate-x-0';
    $isRtl = __('filament-panels::layout.direction') === 'rtl';
@endphp

<aside
    x-data="{}"
    @if (filament()->isSidebarCollapsibleOnDesktop() && (! filament()->hasTopNavigation()))
        x-cloak
        x-bind:class="
            $store.sidebar.isOpen
                ? @js($openSidebarClasses . ' ' . 'lg:sticky')
                : '-translate-x-full rtl:translate-x-full lg:sticky lg:translate-x-0 rtl:lg:-translate-x-0'
        "
    @else
        @if (filament()->hasTopNavigation())
            x-cloak
            x-bind:class="$store.sidebar.isOpen ? @js($openSidebarClasses) : '-translate-x-full rtl:translate-x-full'"
        @elseif (filament()->isSidebarFullyCollapsibleOnDesktop())
            x-cloak
            x-bind:class="$store.sidebar.isOpen ? @js($openSidebarClasses . ' ' . 'lg:sticky') : '-translate-x-full rtl:translate-x-full'"
        @else
            x-cloak="-lg"
            x-bind:class="
                $store.sidebar.isOpen
                    ? @js($openSidebarClasses . ' ' . 'lg:sticky')
                    : 'w-[--sidebar-width] -translate-x-full rtl:translate-x-full lg:sticky'
            "
        @endif
    @endif
    {{
        $attributes->class([
            'fi-sidebar fixed inset-y-0 start-0 z-30 flex h-screen flex-col border-r border-border bg-sidebar transition-all lg:z-0',
            'lg:translate-x-0 rtl:lg:-translate-x-0' => ! (filament()->isSidebarCollapsibleOnDesktop() || filament()->isSidebarFullyCollapsibleOnDesktop() || filament()->hasTopNavigation()),
            'lg:-translate-x-full rtl:lg:translate-x-full' => filament()->hasTopNavigation(),
        ])
    }}
>
    {{-- Header Sidebar ala Shadcn --}}
    <div class="flex h-16 shrink-0 items-center px-6 border-b border-border bg-sidebar">
        <a {{ \Filament\Support\generate_href_html(filament()->getHomeUrl()) }} class="flex items-center gap-2 font-bold text-foreground">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                <x-heroicon-o-building-office-2 class="h-5 w-5" />
            </div>
            <span>{{ filament()->getBrandName() }}</span>
        </a>
    </div>

    {{-- Navigasi --}}
    <nav class="flex flex-1 flex-col overflow-y-auto px-4 py-4 space-y-1">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_START) }}

        @foreach ($navigation as $group)
            <div class="mb-4">
                @if ($group->getLabel())
                    <h4 class="mb-2 px-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                        {{ $group->getLabel() }}
                    </h4>
                @endif
                <ul class="space-y-1">
                    @foreach ($group->getItems() as $item)
                        <x-filament-panels::sidebar.item
                            :active="$item->isActive()"
                            :active-icon="$item->getActiveIcon()"
                            :badge="$item->getBadge()"
                            :badge-color="$item->getBadgeColor()"
                            :icon="$item->getIcon()"
                            :label="$item->getLabel()"
                            :url="$item->getUrl()"
                        />
                    @endforeach
                </ul>
            </div>
        @endforeach

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_NAV_END) }}

        {{-- Topbar hooks (TOPBAR_BEFORE/AFTER handled in layout) --}}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_BEFORE) }}

        {{-- Topbar action placeholder untuk dropdown user --}}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_AFTER) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIDEBAR_FOOTER) }}
    </nav>
</aside>