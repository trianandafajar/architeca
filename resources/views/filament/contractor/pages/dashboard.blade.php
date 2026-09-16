@php
    $formatCurrency = static function ($value): string {
        return 'IDR ' . number_format((float) $value, 0, ',', '.');
    };

    $statusClasses = [
        'active' => 'bg-green-50 text-green-700 ring-green-600/20',
        'planning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'on_hold' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20',
    ];
@endphp

<div class="architeca-dashboard space-y-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">Contractor dashboard</h1>
            <p class="mt-1 text-sm text-muted-foreground">Pantau project yang Anda miliki atau yang menugaskan Anda sebagai anggota.</p>
        </div>
        <p class="text-sm text-muted-foreground">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'My projects', 'value' => $totalProjects, 'caption' => $activeProjects . ' active', 'icon' => 'building-office-2', 'color' => 'text-primary'],
            ['label' => 'Contract value', 'value' => $formatCurrency($totalBudget), 'caption' => 'Across your projects', 'icon' => 'banknotes', 'color' => 'text-blue-600'],
            ['label' => 'Total expenses', 'value' => $formatCurrency($totalExpenses), 'caption' => $budgetUsedPercent . '% of contract value', 'icon' => 'receipt-percent', 'color' => 'text-red-600'],
            ['label' => 'Average progress', 'value' => $avgProgress . '%', 'caption' => 'Latest updates', 'icon' => 'chart-bar', 'color' => 'text-amber-600'],
        ] as $card)
            <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-foreground">{{ $card['value'] }}</p>
                    </div>
                    <x-dynamic-component :component="'heroicon-o-' . $card['icon']" class="h-6 w-6 {{ $card['color'] }}" />
                </div>
                <p class="mt-3 text-xs text-muted-foreground">{{ $card['caption'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between border-b border-border px-6 py-4">
                <div>
                    <h2 class="text-base font-semibold text-foreground">My projects</h2>
                    <p class="mt-1 text-xs text-muted-foreground">Project yang dapat Anda akses.</p>
                </div>
                <a href="{{ route('filament.contractor.resources.projects.index') }}" class="text-sm font-medium text-primary hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px]">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted-foreground">Value</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted-foreground">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($projects->take(6) as $project)
                            @php
                                $progressItem = $latestProgress->first(fn ($item) => $item['project']->id === $project->id);
                                $progress = $progressItem['percentage'] ?? 0;
                            @endphp
                            <tr class="transition-colors hover:bg-muted/30">
                                <td class="px-6 py-4">
                                    <a href="{{ route('filament.contractor.resources.projects.view', $project) }}" class="text-sm font-medium text-foreground hover:text-primary">{{ $project->name }}</a>
                                    <p class="mt-1 text-xs text-muted-foreground">{{ $project->client_name ?: 'No client specified' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClasses[$project->status] ?? 'bg-gray-50 text-gray-700 ring-gray-600/20' }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-foreground">{{ $formatCurrency($project->contract_value) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="h-1.5 w-20 overflow-hidden rounded-full bg-muted"><div class="h-full rounded-full bg-primary" style="width: {{ min(100, max(0, $progress)) }}%"></div></div>
                                        <span class="text-xs font-semibold text-muted-foreground">{{ number_format($progress, 0) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-muted-foreground">Belum ada project yang dapat diakses.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
            <div class="border-b border-border px-6 py-4">
                <h2 class="text-base font-semibold text-foreground">Recent activity</h2>
                <p class="mt-1 text-xs text-muted-foreground">Laporan terbaru dari project Anda.</p>
            </div>
            <div class="divide-y divide-border">
                @forelse ($recentActivity as $activity)
                    <div class="flex items-start gap-3 px-6 py-4">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10"><x-heroicon-o-document-text class="h-4 w-4 text-primary" /></div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-foreground">{{ $activity->project?->name ?? 'Project' }}</p>
                            <p class="mt-1 line-clamp-2 text-xs text-muted-foreground">{{ $activity->work_description ?: 'No description' }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ $activity->user?->name ?? 'Team member' }} · {{ $activity->report_date?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-sm text-muted-foreground">Belum ada aktivitas terbaru.</div>
                @endforelse
            </div>
        </div>
    </div>

    @if ($latestProgress->isNotEmpty())
        <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
            <h2 class="text-base font-semibold text-foreground">Progress by project</h2>
            <p class="mt-1 text-xs text-muted-foreground">Progress terakhir untuk setiap project yang Anda akses.</p>
            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latestProgress as $item)
                    <div class="rounded-lg border border-border p-4">
                        <div class="flex items-center justify-between gap-3"><p class="truncate text-sm font-medium text-foreground">{{ $item['project']->name }}</p><span class="text-xs font-semibold text-primary">{{ number_format($item['percentage'], 0) }}%</span></div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted"><div class="h-full rounded-full bg-primary" style="width: {{ min(100, max(0, $item['percentage'])) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
