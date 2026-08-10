<div class="space-y-4" wire:poll.10s>
    @if (session('special_status'))
        <div class="alert alert-success py-2 text-sm"><i class="ri-checkbox-circle-line"></i><span>{{ session('special_status') }}</span></div>
    @endif

    <div class="flex items-center gap-3">
        <span class="text-sm font-semibold"><i class="ri-customer-service-2-line text-primary"></i> Permintaan Ditugaskan ke Saya</span>
        <span class="badge badge-success badge-sm ml-auto">Selesai hari ini: {{ $doneToday }}</span>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        @forelse ($assigned as $req)
            <div class="card border border-warning/40 bg-warning/5 rounded-xl">
                <div class="card-body gap-2 p-4">
                    <div class="flex items-center gap-2">
                        <span class="badge badge-ghost badge-sm">{{ $req->category->label() }}</span>
                        <span class="text-xs text-secondary">Meja {{ $req->table_code ?? '-' }} · {{ $req->requested_by ?? 'Tamu' }}</span>
                    </div>
                    <p class="text-sm">{{ $req->description }}</p>
                    <x-button variant="success" size="sm" :block="true" icon="ri-check-double-line"
                        wire:click="complete('{{ $req->id }}')" loading="complete('{{ $req->id }}')">
                        Tandai Selesai
                    </x-button>
                </div>
            </div>
        @empty
            <p class="text-center text-sm text-secondary py-8 sm:col-span-2">Tidak ada permintaan aktif untuk Anda.</p>
        @endforelse
    </div>
</div>
