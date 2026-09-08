<div class="space-y-5">
    {{-- Kartu ketiga ("Log layanan") dihapus: isinya $recentServices->count(), yaitu
         panjang daftar yang dibatasi 8 baris — bukan jumlah log yang sebenarnya. --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <x-stat-card title="Total tip hari ini" color="success"
            :value="'Rp ' . number_format((float) $tipsTotal, 0, ',', '.')" />

        <x-stat-card title="Jumlah tip hari ini" :value="number_format((float) $tipsCount, 0, ',', '.')" />
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        {{-- Tip form --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold flex items-center gap-2"><i class="ri-hand-coin-line text-primary"></i> Catat tip</h3>

            @if (session('tip_success'))
                <div class="alert alert-success py-2 text-sm mt-3"><span>{{ session('tip_success') }}</span></div>
            @endif

            <form wire:submit="saveTip" class="mt-4 space-y-3">
                <x-input label="Nominal (Rp)" name="tipAmount" type="number" min="1" step="500" inputmode="numeric"
                    placeholder="0" wire:model="tipAmount" required />
                <div class="grid grid-cols-2 gap-3">
                    <x-select label="Meja (opsional)" name="tipTableId" placeholder="Tanpa meja"
                        wire:model="tipTableId"
                        :options="$tables->mapWithKeys(fn ($t) => [$t->id => $t->code . ' - ' . $t->name])->all()" />

                    <x-select label="Pesanan (opsional)" name="tipOrderId" placeholder="Tanpa pesanan"
                        wire:model="tipOrderId"
                        :options="$activeOrders->mapWithKeys(fn ($o) => [$o->id => '#' . $o->order_number])->all()" />
                </div>
                <x-input label="Catatan (opsional)" name="tipNote" maxlength="255"
                    placeholder="cth: pelanggan ramah" wire:model="tipNote" />
                <x-button type="submit" variant="primary" :block="true" loading="saveTip">Simpan tip</x-button>
            </form>
        </div>

        {{-- Service log form --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold flex items-center gap-2"><i class="ri-customer-service-2-line text-primary"></i> Catat log layanan</h3>

            @if (session('svc_success'))
                <div class="alert alert-success py-2 text-sm mt-3"><span>{{ session('svc_success') }}</span></div>
            @endif

            <form wire:submit="saveService" class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <x-select label="Jenis layanan" name="svcType" wire:model="svcType"
                        :options="$serviceTypes" />

                    <x-select label="Meja (opsional)" name="svcTableId" placeholder="Tanpa meja"
                        wire:model="svcTableId"
                        :options="$tables->mapWithKeys(fn ($t) => [$t->id => $t->code . ' - ' . $t->name])->all()" />
                </div>
                <x-textarea label="Deskripsi (opsional)" name="svcDescription" rows="2" maxlength="500"
                    placeholder="Detail layanan..." wire:model="svcDescription" />
                <x-button type="submit" variant="neutral" :block="true" loading="saveService">Simpan log</x-button>
            </form>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold text-sm mb-3">Tip terakhir</h3>
            <div class="space-y-2">
                @forelse ($recentTips as $tip)
                    <div class="flex items-center justify-between text-sm border-b border-base-200 pb-2">
                        <div>
                            <span class="font-semibold tabular-nums text-success">Rp {{ number_format((float) $tip->amount, 0, ',', '.') }}</span>
                            <span class="text-secondary">· {{ $tip->table?->code ?? 'Umum' }}</span>
                            @if ($tip->note) <p class="text-xs text-secondary">{{ $tip->note }}</p> @endif
                        </div>
                        <span class="text-xs text-secondary">{{ $tip->received_at?->format('d/m H:i') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-secondary">Belum ada tip.</p>
                @endforelse
            </div>
        </div>

        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold text-sm mb-3">Log layanan terakhir</h3>
            <div class="space-y-2">
                @forelse ($recentServices as $log)
                    <div class="flex items-center justify-between text-sm border-b border-base-200 pb-2">
                        <div>
                            <x-badge color="ghost" size="sm">{{ $log->type->label() }}</x-badge>
                            <span class="text-secondary">· {{ $log->table?->code ?? 'Umum' }}</span>
                            @if ($log->description) <p class="text-xs text-secondary">{{ $log->description }}</p> @endif
                        </div>
                        <span class="text-xs text-secondary">{{ $log->served_at?->format('d/m H:i') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-secondary">Belum ada log layanan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
