<div wire:key="song-request">
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-semibold"><i class="ri-music-2-line text-secondary"></i> Request Lagu</span>
        <span class="badge badge-sm {{ $activeCount >= $queueMax ? 'badge-error' : 'badge-ghost' }}">
            {{ $activeCount }}/{{ $queueMax }} antrean
        </span>
    </div>

    @if (! $sessionId)
        <div class="alert alert-info text-sm"><i class="ri-qr-scan-2-line"></i><span>Scan QR meja untuk request lagu.</span></div>
    @else
        @if (session('song_status'))
            <div class="alert alert-success py-1.5 text-xs mb-2"><span>{{ session('song_status') }}</span></div>
        @endif

        <form wire:submit="submit" class="space-y-2">
            <input type="text" wire:model="title" placeholder="Judul lagu" class="input input-bordered input-sm w-full">
            @error('title') <span class="text-error text-xs">{{ $message }}</span> @enderror
            <div class="flex gap-2">
                <input type="text" wire:model="artist" placeholder="Artis (opsional)" class="input input-bordered input-sm grow">
                <input type="text" wire:model="requestedBy" placeholder="Nama (opsional)" class="input input-bordered input-sm grow">
            </div>
            <button type="submit" class="btn btn-secondary btn-sm w-full" @disabled($activeCount >= $queueMax)>
                <i class="ri-add-line"></i> Tambah ke Antrean
            </button>
        </form>

        @if ($mine->isNotEmpty())
            <ul class="mt-3 space-y-1.5">
                @foreach ($mine as $song)
                    @php
                        $badge = match ($song->status) {
                            'playing' => 'badge-success',
                            'queued' => 'badge-warning',
                            'rejected' => 'badge-error',
                            default => 'badge-ghost',
                        };
                    @endphp
                    <li class="flex items-center justify-between text-sm rounded-lg bg-base-200/60 px-3 py-1.5">
                        <span class="truncate">
                            <span class="font-medium">{{ $song->title }}</span>
                            @if ($song->artist)<span class="text-secondary text-xs"> — {{ $song->artist }}</span>@endif
                        </span>
                        <span class="badge {{ $badge }} badge-sm shrink-0">{{ $song->status }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
