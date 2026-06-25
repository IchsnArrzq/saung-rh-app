<div class="space-y-4" wire:poll.10s>
    @if (session('special_status'))
        <div class="alert alert-success py-2 text-sm"><i class="ri-checkbox-circle-line"></i><span>{{ session('special_status') }}</span></div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 card border border-base-300 bg-base-100 rounded-xl">
            <div class="card-body gap-3">
                <h3 class="card-title text-base"><i class="ri-inbox-line text-warning"></i> Menunggu Persetujuan</h3>

                @forelse ($pending as $req)
                    <div class="rounded-lg border border-base-300 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-ghost badge-sm">{{ \App\Models\SpecialRequest::CATEGORIES[$req->category] ?? $req->category }}</span>
                                    <span class="text-xs text-secondary">Meja {{ $req->table_code ?? '-' }} · {{ $req->requested_by ?? 'Tamu' }}</span>
                                    @if ($req->is_paid)<span class="badge badge-secondary badge-sm">Berbayar</span>@endif
                                </div>
                                <p class="text-sm mt-1">{{ $req->description }}</p>
                            </div>
                            <div class="flex gap-1 shrink-0">
                                <button wire:click="approve('{{ $req->id }}')" class="btn btn-xs btn-success">Setujui</button>
                                <button wire:click="reject('{{ $req->id }}')" class="btn btn-xs btn-outline btn-error">Tolak</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-secondary py-8">Tidak ada permintaan menunggu.</p>
                @endforelse
            </div>
        </div>

        <div class="card border border-base-300 bg-base-100 rounded-xl">
            <div class="card-body gap-2">
                <h3 class="card-title text-sm"><i class="ri-history-line"></i> Terbaru</h3>
                @forelse ($recent as $req)
                    @php
                        $badge = match ($req->status) {
                            'done' => 'badge-success',
                            'rejected' => 'badge-error',
                            default => 'badge-info',
                        };
                    @endphp
                    <div class="text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate">{{ $req->description }}</span>
                            <span class="badge {{ $badge }} badge-sm shrink-0">{{ $req->status }}</span>
                        </div>
                        @if ($req->assignee)
                            <span class="text-xs text-secondary">→ {{ $req->assignee->name }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-secondary">Belum ada.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
