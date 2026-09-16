@php
function formatCurrency($value) {
if ($value >= 1000000000) return '$' . number_format($value / 1000000000, 1) . 'B';
if ($value >= 1000000) return '$' . number_format($value / 1000000, 1) . 'M';
if ($value >= 1000) return '$' . number_format($value / 1000, 1) . 'K';
return '$' . number_format($value, 0);
}
@endphp

<div class="architeca-dashboard space-y-8">

    {{-- header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">Dashboard</h1>
            <p class="mt-1 text-sm text-muted-foreground">Welcome back, {{ auth()->user()->name }}. Here's an overview
                of your projects.</p>
        </div>
        <div class="text-sm text-muted-foreground">
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    {{-- stat cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Projects</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ $totalProjects }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                    <x-heroicon-o-building-office-2 class="h-5 w-5 text-primary" />
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs">
                <span
                    class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 font-medium text-green-700">
                    {{ $activeProjects }} active
                </span>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Budget</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ formatCurrency($totalBudget) }}
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                    <x-heroicon-o-banknotes class="h-5 w-5 text-blue-600" />
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-muted-foreground">
                <span>Across all projects</span>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Expenses</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ formatCurrency($totalExpenses)
                        }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-500/10">
                    <x-heroicon-o-receipt-percent class="h-5 w-5 text-red-600" />
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs">
                <span
                    class="{{ ($budgetUsedPercent ?? 0) > 90 ? 'font-medium text-red-600' : 'text-muted-foreground' }}">
                    {{ $budgetUsedPercent ?? 0 }}% of budget
                </span>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Average Progress</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ $avgProgress }}%</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/10">
                    <x-heroicon-o-chart-bar class="h-5 w-5 text-amber-600" />
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

    {{-- charts --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

        {{-- bar chart --}}
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-foreground">Expenses by Category</h3>
            <p class="mb-4 text-xs text-muted-foreground">Spending breakdown across all projects.</p>
            <div class="relative" style="height:300px">
                <canvas id="expenseBarChart"></canvas>
            </div>
        </div>

        {{-- line chart --}}
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-foreground">Budget Usage</h3>
            <p class="mb-4 text-xs text-muted-foreground">Monthly expense trend across projects.</p>
            <div class="relative" style="height:300px">
                <canvas id="budgetLineChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        <div class="xl:col-span-2 rounded-lg border border-border bg-card shadow-sm">
            <div class="flex items-center justify-between border-b border-border px-6 py-4">
                <h3 class="text-sm font-semibold text-foreground">Recent Projects</h3>
                <a href="admin/projects"
                    class="text-xs font-medium text-primary hover:underline">
                    View all
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
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
                                Budget</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($recentProjects as $project)
                        @php $progress = $project->progressUpdates->sortByDesc('progress_date')->first()?->percentage ??
                        0; @endphp
                        <tr class="transition-colors hover:bg-muted/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-foreground">{{
                                $project->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">{{
                                $project->client_name ?? '—' }}</td>
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
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-foreground">{{
                                formatCurrency($project->contract_value) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-muted">
                                        <div class="h-full rounded-full bg-primary" style="width: {{ $progress }}%">
                                        </div>
                                    </div>
                                    <span class="text-xs font-medium text-muted-foreground">{{ number_format($progress,
                                        0) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-muted-foreground">No projects
                                found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-card shadow-sm">
            <div class="border-b border-border px-6 py-4">
                <h3 class="text-sm font-semibold text-foreground">Recent Activity</h3>
            </div>
            <div class="divide-y divide-border max-h-[480px] overflow-y-auto">
                @forelse ($recentActivity as $activity)
                <div class="flex items-start gap-3 px-6 py-4 transition-colors hover:bg-muted/50">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10">
                        <x-heroicon-o-document-text class="h-4 w-4 text-primary" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-foreground truncate">{{ $activity->project->name ?? 'Project'
                            }}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground line-clamp-2">{{ $activity->work_description ??
                            'No description' }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ $activity->user->name ?? 'Staff' }} · {{ $activity->report_date->diffForHumans() }}
                        </p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center text-sm text-muted-foreground">No recent activity.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- progress by project --}}
    @if ($latestProgress->count())
    <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-foreground">Progress by Project</h3>
            <p class="mt-1 text-xs text-muted-foreground">Current completion status for each project.</p>
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

<script src="{{ asset('js/vendor/chart.umd.js') }}"></script>
<script>
    const expenseData = {!! json_encode($expensesByCategory) !!};
    const monthlyData  = {!! json_encode($monthlyExpenses) !!};

    const brownPalette = [
        'rgba(139, 69, 19, 0.85)',
        'rgba(192, 135, 90, 0.80)',
        'rgba(212, 165, 116, 0.75)',
        'rgba(103, 45, 15, 0.80)',
    ];

    const gridColor = '#e5ddd3';

    // --- bar chart ---
    new Chart(document.getElementById('expenseBarChart'), {
        type: 'bar',
        data: {
            labels: expenseData.map(d => d.label),
            datasets: [{
                label: 'Amount (USD)',
                data: expenseData.map(d => d.value),
                backgroundColor: expenseData.map((_, i) => brownPalette[i % brownPalette.length]),
                borderRadius: 6,
                maxBarThickness: 48,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const v = ctx.parsed.y;
                            if (v >= 1e9) return '$' + (v / 1e9).toFixed(1) + 'B';
                            if (v >= 1e6) return '$' + (v / 1e6).toFixed(1) + 'M';
                            if (v >= 1e3) return '$' + (v / 1e3).toFixed(1) + 'K';
                            return '$' + v;
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false },
                    border: { display: false },
                    ticks: {
                        color: '#8b7355',
                        font: { size: 11 },
                        callback: v => {
                            if (v >= 1e9) return '$' + (v / 1e9).toFixed(0) + 'B';
                            if (v >= 1e6) return '$' + (v / 1e6).toFixed(0) + 'M';
                            if (v >= 1e3) return '$' + (v / 1e3).toFixed(0) + 'K';
                            return '$' + v;
                        },
                    },
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#8b7355', font: { size: 11 } },
                },
            },
        },
    });

    // --- line chart ---
    new Chart(document.getElementById('budgetLineChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.label),
            datasets: [{
                label: 'Expenses (USD)',
                data: monthlyData.map(d => d.value),
                borderColor: '#8b4513',
                backgroundColor: 'rgba(139, 69, 19, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#8b4513',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2d1810',
                    titleColor: '#f5e6d3',
                    bodyColor: '#ffffff',
                    borderColor: '#462012',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 6,
                    callbacks: {
                        label: ctx => {
                            const v = ctx.parsed.y;
                            if (v >= 1e6) return '$' + (v / 1e6).toFixed(1) + 'M';
                            if (v >= 1e3) return '$' + (v / 1e3).toFixed(1) + 'K';
                            return '$' + v;
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false },
                    border: { display: false },
                    ticks: {
                        color: '#8b7355',
                        font: { size: 11 },
                        callback: v => {
                            if (v >= 1e6) return '$' + (v / 1e6).toFixed(0) + 'M';
                            if (v >= 1e3) return '$' + (v / 1e3).toFixed(0) + 'K';
                            return '$' + v;
                        },
                    },
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#8b7355', font: { size: 11 } },
                },
            },
        },
    });
</script>
