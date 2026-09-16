<x-filament-panels::page>

    @php
        function formatCurrency($value) {
            if ($value >= 1000000000) return '$' . number_format($value / 1000000000, 1) . 'B';
            if ($value >= 1000000) return '$' . number_format($value / 1000000, 1) . 'M';
            if ($value >= 1000) return '$' . number_format($value / 1000, 1) . 'K';
            return '$' . number_format($value, 0);
        }
    @endphp

    {{-- Summary Cards --}}
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
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
        </div>
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Budget</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ formatCurrency($totalBudget) }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/10">
                    <x-heroicon-o-banknotes class="h-5 w-5 text-green-600" />
                </div>
            </div>
        </div>
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Total Expenses</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ formatCurrency($totalExpenses) }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-500/10">
                    <x-heroicon-o-receipt-percent class="h-5 w-5 text-red-600" />
                </div>
            </div>
        </div>
        <div class="rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Average Progress</p>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ $avgProgress }}%</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                    <x-heroicon-o-chart-bar class="h-5 w-5 text-blue-600" />
                </div>
            </div>
        </div>
    </div>

    {{-- Budget vs Expense --}}
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="flex items-center justify-between border-b border-border px-6 py-4">
            <h3 class="text-sm font-semibold text-foreground">Budget vs Expense</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Budget</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Expenses</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Usage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($budgetVsExpense as $row)
                        @php $pct = $row['budget'] > 0 ? round(($row['expense'] / $row['budget']) * 100) : 0; @endphp
                        <tr class="transition-colors hover:bg-muted/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-foreground">{{ $row['name'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-foreground">{{ formatCurrency($row['budget']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-foreground">{{ formatCurrency($row['expense']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium {{ $row['remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ formatCurrency($row['remaining']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-muted">
                                        <div class="h-full rounded-full {{ $pct > 90 ? 'bg-red-500' : ($pct > 70 ? 'bg-yellow-500' : 'bg-primary') }}" style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-muted-foreground tabular-nums">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-muted-foreground">No budget data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Progress Report --}}
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="flex items-center justify-between border-b border-border px-6 py-4">
            <h3 class="text-sm font-semibold text-foreground">Progress Report</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Latest Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Last Updated</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($progressReport as $row)
                        <tr class="transition-colors hover:bg-muted/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-foreground">{{ $row['project'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $row['progress'] >= 100 ? 'bg-green-50 text-green-700 ring-1 ring-green-600/20' : ($row['progress'] >= 50 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' : 'bg-red-50 text-red-700 ring-1 ring-red-600/20') }}">{{ $row['progress'] }}%</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">{{ $row['date'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">{{ $row['by'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-muted-foreground">No progress recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daily Activity --}}
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <div class="flex items-center justify-between border-b border-border px-6 py-4">
            <h3 class="text-sm font-semibold text-foreground">Latest Daily Activity</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Staff</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($dailyActivity as $row)
                        @php $pg = $row['progress']; @endphp
                        <tr class="transition-colors hover:bg-muted/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-foreground">{{ $row['date'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-foreground">{{ $row['project'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">{{ $row['staff'] }}</td>
                            <td class="px-6 py-4 text-sm text-muted-foreground" title="{{ $row['desc'] }}">{{ Str::limit($row['desc'], 100) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pg >= 100 ? 'bg-green-50 text-green-700 ring-1 ring-green-600/20' : ($pg >= 50 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' : 'bg-red-50 text-red-700 ring-1 ring-red-600/20') }}">{{ $pg }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-muted-foreground">No daily activity recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-filament-panels::page>