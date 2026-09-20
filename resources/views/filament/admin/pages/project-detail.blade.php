<x-filament-panels::page>

    <div class="mb-6 flex flex-wrap gap-2 rounded-xl bg-white p-4 shadow-sm">
        <button wire:click="$set('tab', 'overview')" class="rounded-lg px-4 py-2 text-sm font-medium transition @if($tab === 'overview') bg-primary-500 text-white @else bg-gray-100 text-gray-700 hover:bg-gray-200 @endif">Overview</button>
        <button wire:click="$set('tab', 'rab')" class="rounded-lg px-4 py-2 text-sm font-medium transition @if($tab === 'rab') bg-primary-500 text-white @else bg-gray-100 text-gray-700 hover:bg-gray-200 @endif">Budget</button>
        <button wire:click="$set('tab', 'progress')" class="rounded-lg px-4 py-2 text-sm font-medium transition @if($tab === 'progress') bg-primary-500 text-white @else bg-gray-100 text-gray-700 hover:bg-gray-200 @endif">Progress</button>
        <button wire:click="$set('tab', 'daily')" class="rounded-lg px-4 py-2 text-sm font-medium transition @if($tab === 'daily') bg-primary-500 text-white @else bg-gray-100 text-gray-700 hover:bg-gray-200 @endif">Daily Report</button>
        <button wire:click="$set('tab', 'expenses')" class="rounded-lg px-4 py-2 text-sm font-medium transition @if($tab === 'expenses') bg-primary-500 text-white @else bg-gray-100 text-gray-700 hover:bg-gray-200 @endif">Expenses</button>
        <button wire:click="$set('tab', 'members')" class="rounded-lg px-4 py-2 text-sm font-medium transition @if($tab === 'members') bg-primary-500 text-white @else bg-gray-100 text-gray-700 hover:bg-gray-200 @endif">Project Members</button>
    </div>

    @php
        $project = $record;
        $formatCurrency = fn ($value) => '$' . number_format($value, 0, '.', ',');
    @endphp

    @if ($tab === 'overview')
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Project Overview</h2>
            <dl class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm text-gray-500">Project Name</dt>
                    <dd class="mt-1 font-semibold text-gray-800">{{ $project->name }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm text-gray-500">Client</dt>
                    <dd class="mt-1 font-semibold text-gray-800">{{ $project->client_name ?? '-' }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm text-gray-500">Location</dt>
                    <dd class="mt-1 font-semibold text-gray-800">{{ $project->location ?? '-' }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm text-gray-500">Contract Value</dt>
                    <dd class="mt-1 font-semibold text-gray-800">{{ $formatCurrency($project->contract_value) }}</dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ match($project->status) { 'active' => 'bg-green-100 text-green-800', 'planning' => 'bg-yellow-100 text-yellow-800', 'on_hold' => 'bg-blue-100 text-blue-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-800' } }}">
                        {{ ucfirst($project->status) }}
                    </dd>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <dt class="text-sm text-gray-500">Duration</dt>
                    <dd class="mt-1 font-semibold text-gray-800">{{ $project->start_date?->format('M d, Y') }} — {{ $project->end_date?->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>
    @endif

    @if ($tab === 'rab')
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Budget</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Item</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Qty</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Unit</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Unit Price</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($project->budgetItems as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $item->item_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $item->unit }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $formatCurrency($item->unit_price) }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $formatCurrency($item->total_price) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">No budget data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'progress')
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Progress History</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Progress</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">By</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($project->progressUpdates as $pu)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $pu->progress_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $pu->percentage >= 100 ? 'bg-green-100 text-green-800' : ($pu->percentage >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">{{ $pu->percentage }}%</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $pu->user->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($pu->notes, 60) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No progress history found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'daily')
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Daily Report</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Workers</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Description</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($project->dailyReports as $dr)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $dr->report_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $dr->workers_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($dr->work_description, 60) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $dr->progress_percentage }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No daily reports found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'expenses')
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Expenses</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Category</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Description</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($project->expenses as $exp)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $exp->expense_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">{{ ucfirst($exp->category) }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($exp->description, 60) }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $formatCurrency($exp->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No expenses recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'members')
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Project Members</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Name</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Email</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($project->members as $member)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $member->user->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $member->user->email }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-0.5 text-xs font-medium text-primary-800">{{ ucfirst($member->role) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">No members assigned.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-filament-panels::page>