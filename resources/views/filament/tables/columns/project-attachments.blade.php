<div class="flex flex-wrap items-center gap-2 px-3 py-4">
    @forelse ($getRecord()->attachments as $attachment)
        @php
            $url = \Illuminate\Support\Facades\Storage::disk('local')->temporaryUrl($attachment->file_path, now()->addMinutes(5));
            $name = $attachment->caption ?: basename($attachment->file_path);
        @endphp
        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener noreferrer"
            title="{{ $name }}"
            class="text-sm text-primary-600 hover:underline dark:text-primary-400"
        >
            @if (str_starts_with($attachment->file_type ?? '', 'image/'))
                <img src="{{ $url }}" alt="{{ $name }}" class="h-12 w-12 rounded-lg object-cover" loading="lazy" />
            @else
                {{ $name }}
            @endif
        </a>
    @empty
        <span class="text-sm text-gray-400">—</span>
    @endforelse
</div>
