@props(['style' => null, 'display' => null, 'fixed' => null, 'position' => null])

@if (app('impersonate')->isImpersonating())
@php
$target = Filament\Facades\Filament::auth()->user();
$impersonator = app('impersonate')->getImpersonator();
$display = $display ?? Filament\Facades\Filament::getUserName($target);
$display = $display ?: ($target?->name ?? $target?->email ?? 'user');
$fixed = $fixed ?? config('filament-impersonate.banner.fixed', true);
$position = $position ?? config('filament-impersonate.banner.position', 'top');
$borderPosition = $position === 'top' ? 'bottom' : 'top';
@endphp

<style>
    :root {
        --architeca-impersonate-banner-height: 44px;
    }

    html {
        margin-top: var(--architeca-impersonate-banner-height);
    }

    #impersonate-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        min-height: var(--architeca-impersonate-banner-height);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        padding: .35rem 1rem;
        background: var(--primary, #8b4513);
        color: var(--primary-foreground, #fff);
        border-bottom: 1px solid var(--primary-700, #672d0f);
        z-index: 100;
        font-size: .875rem;
    }

    #impersonate-banner strong {
        font-weight: 700;
    }

    #impersonate-banner a {
        display: inline-flex;
        align-items: center;
        border-radius: .4rem;
        padding: .3rem .75rem;
        background: var(--sidebar, #2d1810);
        color: var(--sidebar-foreground, #f5e6d3);
        font-weight: 600;
        text-decoration: none;
    }

    #impersonate-banner a:hover {
        background: var(--primary-900, #462012);
    }

    html {
        margin-top: var(--architeca-impersonate-banner-height);
    }

    @if ($fixed) 
    .fi-main-ctn > header {
        position: fixed !important;
        top: var(--architeca-impersonate-banner-height) !important;
        left: 0;
        right: 0;
        height: 4rem;
        z-index: 35;
    }
    .fi-main-ctn {
        padding-top: calc(var(--architeca-impersonate-banner-height) + 4rem);
    }
    aside.fi-sidebar {
        top: var(--architeca-impersonate-banner-height) !important;
        height: calc(100vh - var(--architeca-impersonate-banner-height)) !important;
        z-index: 40;
    }
    @endif     @media print {
        #impersonate-banner {
            display: none;
        }
    }
    @media (max-width: 640px) {
        #impersonate-banner {
            flex-direction: column;
            gap: 0.25rem;
            padding: 0.25rem;
        }
        #impersonate-banner span {
            font-size: 0.7rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 90%;
        }
        #impersonate-banner a {
            padding: 0.1rem 0.5rem;
            font-size: 0.7rem;
        }
    }
</style>

<div id="impersonate-banner">
    <span>
        You are logged in as <strong>{{ $display }}</strong>
        @if ($impersonator)
        <span aria-hidden="true">&middot;</span>
        impersonating account <strong>{{ $impersonator->name ?? $impersonator->email }}</strong>
        @endif
    </span>
    <a href="{{ route('filament-impersonate.leave') }}">Return to my account</a>
</div>
@endif