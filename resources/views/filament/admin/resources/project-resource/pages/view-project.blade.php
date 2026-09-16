<x-filament-panels::page
    @class([
        'fi-resource-view-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    @php
        $latestProgress = $record->progressUpdates()->latest('progress_date')->value('percentage') ?? 0;
        $totalExpenses = $record->expenses()->sum('amount');
        $membersCount = $record->members()->count();
        $attachmentsCount = $record->attachments()->count();
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    <div class="architeca-project-detail">
        <div class="architeca-kpi-grid" aria-label="Project key performance indicators">
            <div class="architeca-kpi-card architeca-kpi-progress">
                <div class="architeca-kpi-label">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="h-5 w-5" />
                    <span>Progress terkini</span>
                </div>
                <strong>{{ number_format((float) $latestProgress, 0) }}%</strong>
            </div>
            <div class="architeca-kpi-card architeca-kpi-expenses">
                <div class="architeca-kpi-label">
                    <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" />
                    <span>Total expenses</span>
                </div>
                <strong>Rp {{ number_format((float) $totalExpenses, 0, ',', '.') }}</strong>
            </div>
            <div class="architeca-kpi-card architeca-kpi-members">
                <div class="architeca-kpi-label">
                    <x-filament::icon icon="heroicon-o-user-group" class="h-5 w-5" />
                    <span>Members</span>
                </div>
                <strong>{{ number_format($membersCount) }}</strong>
            </div>
            <div class="architeca-kpi-card architeca-kpi-attachments">
                <div class="architeca-kpi-label">
                    <x-filament::icon icon="heroicon-o-paper-clip" class="h-5 w-5" />
                    <span>Attachments</span>
                </div>
                <strong>{{ number_format($attachmentsCount) }}</strong>
            </div>
        </div>

        @if (count($relationManagers))
            <div class="architeca-project-tabs-shell">
                <x-filament-panels::resources.relation-managers
                    :active-locale="isset($activeLocale) ? $activeLocale : null"
                    :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
                    :content-tab-label="$this->getContentTabLabel()"
                    :content-tab-icon="$this->getContentTabIcon()"
                    :content-tab-position="$this->getContentTabPosition()"
                    :managers="$relationManagers"
                    :owner-record="$record"
                    :page-class="static::class"
                >
                    @if ($hasCombinedRelationManagerTabsWithContent)
                        <x-slot name="content">
                            @if ($this->hasInfolist())
                                {{ $this->infolist }}
                            @else
                                {{ $this->form }}
                            @endif
                        </x-slot>
                    @endif
                </x-filament-panels::resources.relation-managers>
            </div>
        @endif
    </div>
</x-filament-panels::page>
