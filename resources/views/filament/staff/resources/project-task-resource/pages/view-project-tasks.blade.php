<x-filament-panels::page>
    @php
    $tasksList = $this->tasks;
    $total = $tasksList->count();
    $doneCount = $tasksList->where('is_completed', true)->count();
    $percent = $total ? round($doneCount / $total * 100) : 0;
    @endphp

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl font-bold">{{ $record->name }}</h2>
                <x-filament::badge :color="in_array($record->status, ['done', 'completed']) ? 'success' : 'warning'">
                    {{ ucfirst($record->status) }}
                </x-filament::badge>
            </div>
            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-m-user" class="h-4 w-4" /> {{ $record->client_name }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-m-calendar-days" class="h-4 w-4" />
                    {{ $record->start_date?->format('d M Y') }} - {{ $record->end_date?->format('d M Y') }}
                </span>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="mb-5">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Tasks</h3>
                    <span class="text-sm text-gray-500">{{ $doneCount }} / {{ $total }} completed</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="h-full rounded-full transition-all duration-500"
                        style="width: {{ $percent }}%; background-color: #F97316;"></div>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($tasks as $task)
                @php
                $isDone = (bool) $task->is_completed;
                $pending = $this->evidence_files[$task->id] ?? null;
                $hasPhoto = filled($task->evidence_path) || $pending;
                $hasNotes = filled($this->notes[$task->id] ?? null);
                $locked = false;
                @endphp

                <div
                    class="rounded-lg border p-4 transition
                        {{ $isDone ? 'border-success-200 bg-success-50/50 dark:border-success-500/20 dark:bg-success-500/5' : 'border-gray-200 dark:border-white/10' }}">

                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:click="toggleTask({{ $task->id }})" @checked($isDone)
                            style="color: #F97316;"
                            class="h-5 w-5 rounded border-gray-300 disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/20 dark:bg-white/5">

                        <span class="flex-1 font-semibold {{ $isDone ? 'text-gray-400 line-through' : '' }}">
                            {{ $task->title }}
                        </span>

                        <x-filament::badge size="sm" :color="$hasPhoto ? 'success' : 'gray'" icon="heroicon-m-photo">
                            Evidence</x-filament::badge>
                        <x-filament::badge size="sm" :color="$hasNotes ? 'success' : 'gray'"
                            icon="heroicon-m-pencil-square">Notes</x-filament::badge>
                    </div>

                    <div class="mt-3 space-y-3 pl-8">
                        @if($task->evidence_path || $pending)
                        <div class="relative inline-block">
                            <img src="{{ $pending ? $pending->temporaryUrl() : asset('storage/'.$task->evidence_path) }}"
                                class="h-24 w-24 rounded-lg object-cover ring-1 ring-gray-950/10">
                            @unless($isDone)
                            <button type="button" wire:click="removeEvidence({{ $task->id }})"
                                class="absolute -right-2 -top-2 rounded-full bg-danger-600 p-1 text-white shadow hover:bg-danger-500">
                                <x-filament::icon icon="heroicon-m-x-mark" class="h-3.5 w-3.5" />
                            </button>
                            @endunless
                        </div>
                        @elseif(!$isDone)
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-gray-300 px-3 py-3 text-sm text-gray-500 hover:border-primary-500 hover:text-primary-600 dark:border-white/20">
                            <x-filament::icon icon="heroicon-o-plus-circle" class="h-5 w-5" />
                            <span wire:loading.remove wire:target="evidence_files.{{ $task->id }}">Add Photo</span>
                            <span wire:loading wire:target="evidence_files.{{ $task->id }}">Uploading...</span>
                            <input type="file" accept="image/*" wire:model="evidence_files.{{ $task->id }}"
                                class="sr-only">
                        </label>
                        @endif

                        <textarea wire:model.live.debounce.500ms="notes.{{ $task->id }}" rows="2"
                            placeholder="Add notes..."
                            @disabled($isDone)
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:disabled:bg-transparent"></textarea>

                        
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>