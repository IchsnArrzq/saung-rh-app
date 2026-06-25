<div class="space-y-4" wire:poll.5s>
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 card border border-base-300 bg-base-100 rounded-xl">
            <div class="card-body gap-3">
                <h3 class="card-title text-base"><i class="ri-play-list-2-line text-secondary"></i> Antrean Lagu</h3>

                @forelse ($queue as $song)
                    <div class="flex items-center gap-3 rounded-lg border border-base-300 p-3
                        {{ $song->status === 'playing' ? 'bg-success/10 border-success/40' : 'bg-base-100' }}">
                        <div class="grow min-w-0">
                            <div class="font-semibold truncate">
                                @if ($song->status === 'playing')<i class="ri-volume-up-line text-success"></i>@endif
                                {{ $song->title }}
                                @if ($song->artist)<span class="text-secondary text-sm font-normal">— {{ $song->artist }}</span>@endif
                            </div>
                            <div class="text-xs text-secondary">
                                Meja {{ $song->table_code ?? '-' }}
                                @if ($song->requested_by) · {{ $song->requested_by }} @endif
                                · {{ $song->created_at?->format('H:i') }}
                            </div>
                        </div>
                        <div class="flex gap-1 shrink-0">
                            @if ($song->status === 'queued')
                                <button wire:click="advance('{{ $song->id }}')" class="btn btn-xs btn-success">Putar</button>
                            @else
                                <button wire:click="advance('{{ $song->id }}')" class="btn btn-xs btn-outline btn-success">Selesai</button>
                            @endif
                            <button wire:click="reject('{{ $song->id }}')" class="btn btn-xs btn-outline btn-error">Tolak</button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-secondary py-8">Tidak ada lagu di antrean.</p>
                @endforelse
            </div>
        </div>

        <div class="card border border-base-300 bg-base-100 rounded-xl">
            <div class="card-body gap-2">
                <h3 class="card-title text-sm"><i class="ri-history-line"></i> Riwayat Terbaru</h3>
                @forelse ($recentDone as $song)
                    <div class="flex items-center justify-between text-sm">
                        <span class="truncate">{{ $song->title }}</span>
                        <span class="badge badge-sm {{ $song->status === 'done' ? 'badge-ghost' : 'badge-error' }}">{{ $song->status }}</span>
                    </div>
                @empty
                    <p class="text-xs text-secondary">Belum ada riwayat.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
