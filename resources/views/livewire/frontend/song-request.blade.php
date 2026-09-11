{{-- Polling 30 dtk hanya cadangan saat Reverb tidak tersambung; `.visible` membuatnya
     diam selama panel meja tertutup. Perubahan normal datang lewat siaran FloorActivity. --}}
<div wire:key="song-request" wire:poll.30s.visible class="space-y-4">
    @if (! $sessionId)
        <x-alert type="info" icon="ri-qr-scan-2-line">Pindai QR di meja Anda untuk meminta lagu.</x-alert>
    @else
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-base-content/70">
                Setiap meja boleh punya <span class="tabular-nums">{{ $queueMax }}</span> lagu aktif di antrean.
            </p>
            <x-badge size="sm" :color="$activeCount >= $queueMax ? 'warning' : 'ghost'" class="shrink-0 tabular-nums">
                {{ $activeCount }}/{{ $queueMax }} terpakai
            </x-badge>
        </div>

        @if (session('song_status'))
            <x-alert type="success">{{ session('song_status') }}</x-alert>
        @endif

        <form wire:submit="submit" class="space-y-3">
            <x-input label="Judul lagu" name="title" placeholder="Mis. Laskar Pelangi" maxlength="120" required
                wire:model="title" />

            <div class="grid grid-cols-2 gap-3">
                <x-input label="Penyanyi" name="artist" placeholder="Opsional" maxlength="120" wire:model="artist" />
                <x-input label="Nama Anda" name="requestedBy" placeholder="Opsional" maxlength="60"
                    wire:model="requestedBy" />
            </div>

            @if ($activeCount >= $queueMax)
                <p class="text-sm text-base-content/70">
                    Antrean meja Anda penuh. Anda bisa meminta lagu lagi setelah salah satu lagu selesai diputar.
                </p>
            @endif

            <x-button type="submit" variant="primary" :block="true" icon="ri-add-line" loading="submit"
                :disabled="$activeCount >= $queueMax" class="min-h-11">
                Tambah ke antrean
            </x-button>
        </form>

        @if ($mine->isNotEmpty())
            <section>
                <h3 class="mb-2 text-sm font-semibold">Lagu dari meja Anda</h3>

                <ul class="divide-y divide-base-300 rounded-xl bg-base-200">
                    @foreach ($mine as $song)
                        <li wire:key="my-song-{{ $song->id }}" class="flex items-center justify-between gap-3 px-3 py-2.5">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium">{{ $song->title }}</span>
                                @if ($song->artist)
                                    <span class="block truncate text-xs text-base-content/60">{{ $song->artist }}</span>
                                @endif
                            </span>
                            <x-status-badge :status="$song->status" size="sm" class="shrink-0" />
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif
</div>
