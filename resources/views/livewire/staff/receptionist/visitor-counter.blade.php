<div wire:poll.30s class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Pengunjung Hari Ini</p>
            <p class="mt-1 text-3xl font-bold text-primary">{{ $todayPax }}</p>
            <p class="text-xs text-secondary">{{ $todayEntries }} kunjungan</p>
        </div>
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Minggu Ini</p>
            <p class="mt-1 text-3xl font-bold">{{ $weekPax }}</p>
            <p class="text-xs text-secondary">{{ $weekEntries }} kunjungan</p>
        </div>
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Dari QR Scan</p>
            <p class="mt-1 text-3xl font-bold text-success">{{ (int) ($bySource['qr']->p ?? 0) }}</p>
            <p class="text-xs text-secondary">{{ (int) ($bySource['qr']->c ?? 0) }} sesi</p>
        </div>
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Walk-in</p>
            <p class="mt-1 text-3xl font-bold text-warning">{{ (int) ($bySource['walk_in']->p ?? 0) }}</p>
            <p class="text-xs text-secondary">{{ (int) ($bySource['walk_in']->c ?? 0) }} catatan</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr_320px]">
        {{-- 7-day trend --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold text-sm mb-4">Tren 7 Hari (pengunjung)</h3>
            <div class="flex items-end justify-between gap-2 h-44">
                @foreach ($series as $day)
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <span class="text-xs font-semibold">{{ $day['pax'] }}</span>
                        <div class="w-full rounded-t bg-primary/80 transition-all"
                            style="height: {{ max(4, (int) round($day['pax'] / $maxPax * 140)) }}px;"
                            title="{{ $day['date'] }}: {{ $day['pax'] }} org / {{ $day['entries'] }} kunjungan"></div>
                        <span class="text-[10px] text-secondary">{{ $day['label'] }}</span>
                        <span class="text-[10px] text-secondary">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Walk-in form --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold flex items-center gap-2"><i class="ri-walk-line text-primary"></i> Catat Walk-in</h3>

            @if (session('visitor_success'))
                <div class="alert alert-success py-2 text-sm mt-3"><span>{{ session('visitor_success') }}</span></div>
            @endif

            <form wire:submit="addWalkIn" class="mt-4 space-y-3">
                <div>
                    <label class="text-xs text-secondary">Jumlah Orang</label>
                    <input type="number" min="1" max="50" wire:model="walkInPax" class="input input-bordered w-full">
                    @error('walkInPax') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-xs text-secondary">Meja (opsional)</label>
                    <select wire:model="walkInTableId" class="select select-bordered w-full">
                        <option value="">—</option>
                        @foreach ($tables as $t)
                            <option value="{{ $t->id }}">{{ $t->code }} - {{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full">
                    <span wire:loading.remove wire:target="addWalkIn">Tambah Pengunjung</span>
                    <span wire:loading wire:target="addWalkIn">Menyimpan...</span>
                </button>
            </form>
        </div>
    </div>
</div>
