<div wire:key="song-request">
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-semibold"><i class="ri-music-2-line text-secondary"></i> Permintaan lagu</span>
        <x-badge size="sm" :color="$activeCount >= $queueMax ? 'error' : 'ghost'" class="tabular-nums">
            {{ $activeCount }}/{{ $queueMax }} antrean
        </x-badge>
    </div>

    @if (! $sessionId)
        <div class="alert alert-info text-sm"><i class="ri-qr-scan-2-line"></i><span>Scan QR meja untuk request lagu.</span></div>
    @else
        @if (session('song_status'))
            <div class="alert alert-success py-1.5 text-xs mb-2"><span>{{ session('song_status') }}</span></div>
        @endif

        <form wire:submit="submit" class="space-y-2">
            <x-input bare size="sm" class="w-full" name="title" label="Judul lagu" placeholder="Judul lagu"
                wire:model="title" />
            @error('title') <span class="text-xs text-error">{{ $message }}</span> @enderror
            <div class="flex gap-2">
                <x-input bare size="sm" class="grow" name="artist" label="Artis" placeholder="Artis (opsional)"
                    wire:model="artist" />
                <x-input bare size="sm" class="grow" name="requestedBy" label="Nama pemesan"
                    placeholder="Nama (opsional)" wire:model="requestedBy" />
            </div>
            <x-button type="submit" variant="secondary" size="sm" :block="true" icon="ri-add-line"
                loading="submit" :disabled="$activeCount >= $queueMax">
                Tambah ke antrean
            </x-button>
        </form>

        @if ($mine->isNotEmpty())
            <ul class="mt-3 space-y-1.5">
                @foreach ($mine as $song)
                    <li class="flex items-center justify-between text-sm rounded-lg bg-base-200/60 px-3 py-1.5">
                        <span class="truncate">
                            <span class="font-medium">{{ $song->title }}</span>
                            @if ($song->artist)<span class="text-secondary text-xs"> — {{ $song->artist }}</span>@endif
                        </span>
                        <x-status-badge :status="$song->status" size="sm" class="shrink-0" />
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
