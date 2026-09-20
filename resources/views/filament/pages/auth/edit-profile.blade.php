<x-filament-panels::page>
    @php($user = auth()->user())

    <div class="pf" x-data="{}" x-on:refresh-header.window="window.location.reload()">
        <aside class="pf-card">
            <div class="pf-head">
                <button type="button" wire:click="mountAction('changeAvatar')" class="pf-avatar" title="Change photo">
                    @if ($user->avatar_url)
                    <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="{{ $user->name }}">
                    @else
                    <span class="pf-initial">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    @endif

                    <span class="pf-overlay">
                        <x-heroicon-m-camera style="width:1.25rem;height:1.25rem" />
                        Change photo
                    </span>
                </button>

                <h2 class="pf-name">{{ $user->name }}</h2>
                <p class="pf-email">{{ $user->email }}</p>

                <div class="pf-badge">
                    <x-filament::badge>{{ ucfirst($user->role) }}</x-filament::badge>
                </div>

                <div class="pf-remove">{{ $this->removeAvatarAction }}</div>
            </div>

            <dl class="pf-stats">
                @foreach (['projects' => 'Projects', 'reports' => 'Reports', 'expenses' => 'Expenses'] as $key =>
                $label)
                <div>
                    <dd>{{ $stats[$key] }}</dd>
                    <dt>{{ $label }}</dt>
                </div>
                @endforeach
            </dl>

            <div class="pf-joined">
                <x-heroicon-m-calendar-days style="width:1rem;height:1rem;flex-shrink:0" />
                Joined {{ $user->created_at->format('M Y') }}
            </div>
        </aside>

        <form wire:submit="save" class="pf-form">
            {{ $this->form }}

            <div class="pf-actions">
                <x-filament::button type="submit" wire:target="save">Save changes</x-filament::button>
            </div>
        </form>
    </div>

    <style>
        .pf {
            --pf-bg: #fff;
            --pf-line: rgba(var(--gray-950), .06);
            --pf-text: rgb(var(--gray-950));
            --pf-muted: rgb(var(--gray-500));
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.5rem;
            align-items: start;
        }

        .dark .pf {
            --pf-bg: rgb(var(--gray-900));
            --pf-line: rgba(255, 255, 255, .1);
            --pf-text: #fff;
            --pf-muted: rgb(var(--gray-400));
        }

        @media (min-width: 1024px) {
            .pf {
                grid-template-columns: 20rem minmax(0, 1fr);
            }

            .pf-card {
                position: sticky;
                top: 6rem;
            }
        }

        .pf-card {
            overflow: hidden;
            border-radius: .75rem;
            background: var(--pf-bg);
            box-shadow: 0 0 0 1px var(--pf-line), 0 1px 2px rgba(0, 0, 0, .04);
        }

        .pf-head {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
        }

        .pf-avatar {
            position: relative;
            width: 7rem;
            height: 7rem;
            margin-bottom: .75rem;
            overflow: hidden;
            border-radius: 9999px;
            box-shadow: 0 0 0 4px var(--pf-bg), 0 4px 10px rgba(0, 0, 0, .12);
            cursor: pointer;
        }

        .pf-avatar:focus-visible {
            outline: 2px solid rgb(var(--primary-500));
            outline-offset: 3px;
        }

        .pf-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .pf-initial {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            background: rgba(var(--primary-500), .12);
            color: rgb(var(--primary-600));
            font-size: 2.25rem;
            font-weight: 600;
        }

        .pf-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .25rem;
            background: rgba(0, 0, 0, .55);
            color: #fff;
            font-size: .75rem;
            font-weight: 500;
            opacity: 0;
            transition: opacity .15s;
        }

        .pf-avatar:hover .pf-overlay,
        .pf-avatar:focus-visible .pf-overlay {
            opacity: 1;
        }

        .pf-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--pf-text);
        }

        .pf-email {
            font-size: .875rem;
            color: var(--pf-muted);
        }

        .pf-badge {
            margin-top: .75rem;
        }

        .pf-remove {
            margin-top: .5rem;
        }

        .pf-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid var(--pf-line);
        }

        .pf-stats>div {
            padding: 1rem .5rem;
            text-align: center;
        }

        .pf-stats>div+div {
            border-left: 1px solid var(--pf-line);
        }

        .pf-stats dd {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--pf-text);
        }

        .pf-stats dt {
            font-size: .75rem;
            color: var(--pf-muted);
        }

        .pf-joined {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--pf-line);
            font-size: .875rem;
            color: var(--pf-muted);
        }

        .pf-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            min-width: 0;
        }

        .pf-actions {
            display: flex;
            justify-content: flex-end;
        }
    </style>
</x-filament-panels::page>