<x-filament-panels::page>
    <div class="space-y-6">
        @php
            function formatCurrency($value) {
                if ($value >= 1000000000) return '$' . number_format($value / 1000000000, 1) . 'B';
                if ($value >= 1000000) return '$' . number_format($value / 1000000, 1) . 'M';
                if ($value >= 1000) return '$' . number_format($value / 1000, 1) . 'K';
                return '$' . number_format($value, 0);
            }
        @endphp

        {{-- Summary Metrics Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Projects</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $totalProjects }}</p>
                    </div>
                    <div class="rounded-lg bg-primary-50 p-3 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400">
                        <x-heroicon-o-building-office-2 class="h-6 w-6" />
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Budget</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ formatCurrency($totalBudget) }}</p>
                    </div>
                    <div class="rounded-lg bg-success-50 p-3 text-success-600 dark:bg-success-400/10 dark:text-success-400">
                        <x-heroicon-o-banknotes class="h-6 w-6 text-success-600" />
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ formatCurrency($totalExpenses) }}</p>
                    </div>
                    <div class="rounded-lg bg-danger-50 p-3 text-danger-600 dark:bg-danger-400/10 dark:text-danger-400">
                        <x-heroicon-o-receipt-percent class="h-6 w-6 text-danger-600" />
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Progress</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $avgProgress }}%</p>
                    </div>
                    <div class="rounded-lg bg-info-50 p-3 text-info-600 dark:bg-info-400/10 dark:text-info-400">
                        <x-heroicon-o-chart-bar class="h-6 w-6 text-info-600" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Budget vs Expense Table Section --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Budget vs Expense Overview</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800/50 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3">Project Name</th>
                            <th class="px-6 py-3">Budget</th>
                            <th class="px-6 py-3">Expenses</th>
                            <th class="px-6 py-3">Remaining</th>
                            <th class="px-6 py-3">Usage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($budgetVsExpense as $row)
                            @php $pct = $row['budget'] > 0 ? round(($row['expense'] / $row['budget']) * 100) : 0; @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                <td class="px-6 py-4">{{ formatCurrency($row['budget']) }}</td>
                                <td class="px-6 py-4">{{ formatCurrency($row['expense']) }}</td>
                                <td class="px-6 py-4 font-medium {{ $row['remaining'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">{{ formatCurrency($row['remaining']) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-full max-w-[100px] overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                            <div class="h-full rounded-full {{ $pct > 90 ? 'bg-danger-500' : ($pct > 70 ? 'bg-warning-500' : 'bg-primary-500') }}" style="width: {{ min($pct, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No budget data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Progress Report Section --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Progress Reports</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800/50 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3">Project</th>
                            <th class="px-6 py-3">Latest Progress</th>
                            <th class="px-6 py-3">Last Updated</th>
                            <th class="px-6 py-3">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($progressReport as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $row['project'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $row['progress'] >= 100 ? 'bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400' : 'bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-400' }}">
                                        {{ $row['progress'] }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $row['date'] }}</td>
                                <td class="px-6 py-4">{{ $row['by'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No progress updates.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Daily Activities Section --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Recent Daily Activities</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800/50 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Project</th>
                            <th class="px-6 py-3">Staff</th>
                            <th class="px-6 py-3">Description</th>
                            <th class="px-6 py-3">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($dailyActivity as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $row['date'] }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ $row['project'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $row['staff'] }}</td>
                                <td class="px-6 py-4">{{ $row['desc'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $row['progress'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No daily activities recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>