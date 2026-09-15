<x-filament-panels::page>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Input Laporan Harian</h1>
        <p class="mt-1 text-gray-500">Isi laporan aktivitas pekerjaan harian di lapangan</p>
    </div>

    {{-- Form --}}
    <div class="rounded-xl bg-white p-6 shadow-sm">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button wire:click="submit" color="primary">
                Kirim Laporan
            </x-filament::button>
        </div>
    </div>

    {{-- Riwayat Laporan --}}
    <div class="mt-8 rounded-xl bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Riwayat Laporan Saya</h2>

        @php
            $history = $this->getReportsHistory();
        @endphp

        @if ($history->isEmpty())
            <p class="text-sm text-gray-500">Belum ada laporan yang dikirim.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Tanggal</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Project</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Pekerja</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Deskripsi</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Progress</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Kendala</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($history as $report)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $report->report_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $report->project?->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $report->workers_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($report->work_description, 60) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $report->progress_percentage }}%</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ Str::limit($report->issues, 40) ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-filament-panels::page>