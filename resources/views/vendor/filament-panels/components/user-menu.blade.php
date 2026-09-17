@php
$user = filament()->auth()->user();
$items = filament()->getUserMenuItems();

$profileItem = $items['profile'] ?? $items['account'] ?? null;
$profileItemUrl = $profileItem?->getUrl();
$profilePage = filament()->getProfilePage();
$hasProfileItem = filament()->hasProfile() || filled($profileItemUrl);

$logoutItem = $items['logout'] ?? null;

$items = \Illuminate\Support\Arr::except($items, ['account', 'logout', 'profile']);
@endphp

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

<x-filament::dropdown placement="bottom-end" teleport :attributes="
        \Filament\Support\prepare_inherited_attributes($attributes)
            ->class(['fi-user-menu'])
    ">
    <x-slot name="trigger">
        <button aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}" type="button"
            class="shrink-0">
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

    @if ($hasProfileItem)
    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item :color="$profileItem?->getColor()"
            :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
            :href="$profileItemUrl ?? filament()->getProfileUrl()"
            :target="($profileItem?->shouldOpenUrlInNewTab() ?? false) ? '_blank' : null" tag="a">
            {{ $profileItem?->getLabel() ?? ($profilePage ? $profilePage::getLabel() : null) ??
            filament()->getUserName($user) }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
    @else
    <x-filament::dropdown.header :color="$profileItem?->getColor()"
        :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'">
        {{ $profileItem?->getLabel() ?? filament()->getUserName($user) }}
    </x-filament::dropdown.header>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
    @endif

    @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
    <x-filament::dropdown.list>
        <x-filament-panels::theme-switcher />
    </x-filament::dropdown.list>
    @endif

    <x-filament::dropdown.list>
        @foreach ($items as $key => $item)
        @php
        $itemPostAction = $item->getPostAction();
        @endphp

        <x-filament::dropdown.list.item :action="$itemPostAction" :color="$item->getColor()" :href="$item->getUrl()"
            :icon="$item->getIcon()" :method="filled($itemPostAction) ? 'post' : null"
            :tag="filled($itemPostAction) ? 'form' : 'a'" :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null">
            {{ $item->getLabel() }}
        </x-filament::dropdown.list.item>
        @endforeach

        <x-filament::dropdown.list.item :color="$logoutItem?->getColor()"
            :icon="$logoutItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.logout-button') ?? 'heroicon-m-arrow-left-on-rectangle'"
            x-on:click="$dispatch('open-modal', { id: 'logout-modal' })">
            {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
</x-filament::dropdown>

<x-filament::modal id="logout-modal" width="md" alignment="center" :close-by-clicking-away="false"
        icon="heroicon-o-arrow-right-start-on-rectangle" icon-color="danger">
        <x-slot name="heading">
            Confirm Logout
        </x-slot>

        <x-slot name="description">
            <div class="space-y-3">
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">You are about to log out from your account.</p>
                <div class="mx-auto flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3 dark:bg-white/5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#8B4513] text-sm font-semibold text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 text-left">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footerActions" class="flex justify-center gap-3">
            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'logout-modal' })">
                Cancel
            </x-filament::button>
            <form method="post" action="{{ $logoutItem?->getUrl() ?? filament()->getLogoutUrl() }}"
                x-data="{ loading: false }" @submit="loading = true" class="contents">
                @csrf
                <x-filament::button color="danger" type="submit" x-bind:disabled="loading"
                    x-bind:class="loading ? 'cursor-wait opacity-70' : ''">
                    <span x-show="! loading" class="flex items-center gap-2">
                        <x-heroicon-o-arrow-right-start-on-rectangle class="h-4 w-4" />
                        Logout
                    </span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <x-heroicon-m-arrow-path class="h-4 w-4 animate-spin text-white" />
                        Logging out...
                    </span>
                </x-filament::button>
            </form>
        </x-slot>
    </x-filament::modal>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}