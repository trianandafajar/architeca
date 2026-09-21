<x-filament-panels::page
    @class([
        'fi-resource-view-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    @php
        $progress = $record->progress;
        $myReportsCount = $record->dailyReports()->where('user_id', auth()->id())->count();
        $membersCount = $record->members()->count();
        $myAttachmentsCount = $record->attachments()->where('user_id', auth()->id())->count();
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    <div class="architeca-project-detail">
        <div class="architeca-kpi-grid" aria-label="Project key performance indicators">
            <div class="architeca-kpi-card architeca-kpi-progress">
                <div class="architeca-kpi-label">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="h-5 w-5" />
                    <span>Real Progress</span>
                </div>
                <x-filament::progress-bar :value="$progress" class="mt-2" />
                <span class="text-sm font-semibold">{{ number_format($progress, 0) }}%</span>
            </div>
            <div class="architeca-kpi-card architeca-kpi-reports">
                <div class="architeca-kpi-label">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5" />
                    <span>My reports</span>
                </div>
                <strong>{{ number_format($myReportsCount) }}</strong>
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
                    <span>My attachments</span>
                </div>
                <strong>{{ number_format($myAttachmentsCount) }}</strong>
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
