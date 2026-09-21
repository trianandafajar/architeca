@php
function formatCurrency($value) {
if ($value >= 1000000000) return '$' . number_format($value / 1000000000, 1) . 'B';
if ($value >= 1000000) return '$' . number_format($value / 1000000, 1) . 'M';
if ($value >= 1000) return '$' . number_format($value / 1000, 1) . 'K';
return '$' . number_format($value, 0);
}
@endphp

<div class="architeca-dashboard space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-foreground mb-2">Staff Dashboard</h1>
            <p class="mt-1 text-sm text-muted-foreground">Welcome back, {{ auth()->user()->name }}. Here is your
                assigned workload overview.</p>
        </div>
        <div class="text-sm text-muted-foreground">
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Assigned Projects</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ $totalProjects }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/10">
                    <x-heroicon-o-building-office-2 class="h-5 w-5 text-orange-600" />
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs">
                <span
                    class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 font-medium text-green-700">
                    {{ $activeProjects }} active
                </span>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">My Tasks Done</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ $completedTasks }} / {{
                        $totalTasks }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/10">
                    <x-heroicon-o-check-circle class="h-5 w-5 text-orange-600" />
                </div>
            </div>
            <div class="mt-4">
                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div class="h-full rounded-full bg-primary transition-all duration-500"
                        style="width: {{ $taskProgress }}%"></div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Assigned Budget</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ formatCurrency($totalBudget) }}
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/10">
                    <x-heroicon-o-banknotes class="h-5 w-5 text-orange-600" />
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-muted-foreground">
                <span>Total contract value</span>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Avg Project Progress</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ $avgProgress }}%</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-500/10">
                    <x-heroicon-o-chart-bar class="h-5 w-5 text-orange-600" />
                </div>
            </div>
            <div class="mt-4">
                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div class="h-full rounded-full bg-primary transition-all duration-500"
                        style="width: {{ $avgProgress }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="flex items-center justify-between border-b border-border px-6 py-4">
            <h3 class="text-sm font-semibold text-foreground">My Assigned Projects</h3>
            <a href="/staff/project-tasks" class="text-xs font-medium text-primary hover:underline">
                View all
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr class="border-b border-border">
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            Project</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            Client</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            Status</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($recentProjects as $project)
                    <tr class="transition-colors hover:bg-muted/50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-foreground">{{ $project->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">{{ $project->client_name
                            ?? '—' }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ match($project->status) {
                                    'active' => 'bg-green-50 text-green-700 ring-1 ring-green-600/20',
                                    'planning' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
                                    'on_hold' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
                                    'completed' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
                                    'cancelled' => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
                                    default => 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20',
                                } }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            @php
                            $compW = $project->tasks->where('is_completed', true)->sum('percentage_weight');
                            $totW = $project->tasks->sum('percentage_weight');
                            $pVal = $totW > 0 ? ($compW / $totW) * 100 : 0;
                            @endphp
                            <div class="flex items-center justify-end gap-2">
                                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-muted">
                                    <div class="h-full rounded-full bg-primary" style="width: {{ $pVal }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-muted-foreground">{{ number_format($pVal, 0)
                                    }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-sm text-muted-foreground">No projects
                            assigned found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($latestProgress->count())
    <div class="rounded-xl border border-border bg-card p-8 shadow-sm">
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-foreground">Assigned Projects Progress</h3>
            <p class="mt-1 text-xs text-muted-foreground">Current completion status for your assigned projects.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestProgress as $prog)
            <div
                class="flex flex-col gap-3 rounded-lg border border-border p-4 transition-all hover:shadow-sm hover:border-primary/30">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">{{ $prog['name'] }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-primary transition-all duration-500"
                            style="width: {{ $prog['progress'] }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-primary tabular-nums">{{ number_format($prog['progress'], 0)
                        }}%</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>