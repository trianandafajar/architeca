<x-filament-panels::page>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Laporan Project</h1>
        <p class="mt-1 text-gray-500">Rangkuman budget, progress, dan aktivitas proyek</p>
    </div>

    {{-- summary cards --}}
    <div class="mb-8 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="text-sm text-gray-500">Total Project</div>
            <div class="mt-2 text-3xl font-bold text-primary-600">{{ $totalProjects }}</div>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="text-sm text-gray-500">Total Budget</div>
            <div class="mt-2 text-3xl font-bold text-green-600">Rp {{ number_format($totalBudget, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="text-sm text-gray-500">Total Expenses</div>
            <div class="mt-2 text-3xl font-bold text-red-600">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="text-sm text-gray-500">Avg Progress</div>
            <div class="mt-2 text-3xl font-bold text-blue-600">{{ $avgProgress }}%</div>
        </div>
    </div>

    {{-- budget vs expense --}}
    <div class="mb-8 rounded-xl bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Budget vs Expense</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Project</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Budget</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Expenses</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Sisa</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($budgetVsExpense as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">Rp {{ number_format($row['budget'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800">Rp {{ number_format($row['expense'], 0, ',', '.') }}
                        </td>
                        <td
                            class="px-4 py-3 text-sm font-medium {{ $row['remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($row['remaining'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $row['budget'] > 0 ? round(($row['expense'] /
                            $row['budget']) * 100) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- progress report --}}
    <div class="mb-8 rounded-xl bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Progress Report</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Project</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Latest Progress</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Tanggal Update</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($progressReport as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $row['project'] }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $row['progress'] >= 100 ? 'bg-green-100 text-green-800' : ($row['progress'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{
                                $row['progress'] }}%</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row['date'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row['by'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- daily activity --}}
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Aktivitas Harian Terbaru</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Project</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Staff</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Deskripsi</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($dailyActivity as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $row['date'] }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $row['project'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row['staff'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($row['desc'], 60) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $row['progress'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-filament-panels::page>