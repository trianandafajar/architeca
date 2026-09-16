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
        :root { --architeca-impersonate-banner-height: 44px; }
        html { margin-{{ $position }}: var(--architeca-impersonate-banner-height); }
        #impersonate-banner {
            position: {{ $fixed ? 'fixed' : 'absolute' }};
            {{ $position }}: 0;
            width: 100%;
            min-height: var(--architeca-impersonate-banner-height);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            padding: .35rem 1rem;
            background: var(--primary, #8b4513);
            color: var(--primary-foreground, #fff);
            border-{{ $borderPosition }}: 1px solid var(--primary-700, #672d0f);
            z-index: 45;
            font-size: .875rem;
        }
        #impersonate-banner strong { font-weight: 700; }
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
        #impersonate-banner a:hover { background: var(--primary-900, #462012); }
        @if ($fixed)
            div.fi-layout > aside.fi-sidebar { height: calc(100vh - var(--architeca-impersonate-banner-height)); }
            @if ($position === 'top')
                .fi-topbar, div.fi-layout > aside.fi-sidebar { top: var(--architeca-impersonate-banner-height); }
            @endif
        @endif
        @media print { #impersonate-banner { display: none; } }
    </style>

    <div id="impersonate-banner">
        <span>
            Anda sedang login sebagai <strong>{{ $display }}</strong>
            @if ($impersonator)
                <span aria-hidden="true">&middot;</span>
                dari akun <strong>{{ $impersonator->name ?? $impersonator->email }}</strong>
            @endif
        </span>
        <a href="{{ route('filament-impersonate.leave') }}">Kembali ke akun saya</a>
    </div>
@endif
