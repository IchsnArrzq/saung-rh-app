<div class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Total Tip Hari Ini</p>
            <p class="mt-1 text-2xl font-bold text-success">Rp {{ number_format((float) $tipsTotal, 0, ',', '.') }}</p>
        </div>
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Jumlah Tip</p>
            <p class="mt-1 text-2xl font-bold">{{ $tipsCount }}</p>
        </div>
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Log Layanan (terakhir)</p>
            <p class="mt-1 text-2xl font-bold">{{ $recentServices->count() }}</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        {{-- Tip form --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold flex items-center gap-2"><i class="ri-hand-coin-line text-primary"></i> Catat Tip</h3>

            @if (session('tip_success'))
                <div class="alert alert-success py-2 text-sm mt-3"><span>{{ session('tip_success') }}</span></div>
            @endif

            <form wire:submit="saveTip" class="mt-4 space-y-3">
                <div>
                    <label class="text-xs text-secondary">Nominal (Rp)</label>
                    <input type="number" min="1" step="500" wire:model="tipAmount" class="input input-bordered w-full" placeholder="0">
                    @error('tipAmount') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-secondary">Meja (opsional)</label>
                        <select wire:model="tipTableId" class="select select-bordered w-full">
                            <option value="">—</option>
                            @foreach ($tables as $t)
                                <option value="{{ $t->id }}">{{ $t->code }} - {{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-secondary">Order (opsional)</label>
                        <select wire:model="tipOrderId" class="select select-bordered w-full">
                            <option value="">—</option>
                            @foreach ($activeOrders as $o)
                                <option value="{{ $o->id }}">#{{ $o->order_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-secondary">Catatan (opsional)</label>
                    <input type="text" wire:model="tipNote" maxlength="255" class="input input-bordered w-full" placeholder="cth: pelanggan ramah">
                </div>
                <button type="submit" class="btn btn-primary w-full">
                    <span wire:loading.remove wire:target="saveTip">Simpan Tip</span>
                    <span wire:loading wire:target="saveTip">Menyimpan...</span>
                </button>
            </form>
        </div>

        {{-- Service log form --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold flex items-center gap-2"><i class="ri-customer-service-2-line text-primary"></i> Catat Log Layanan</h3>

            @if (session('svc_success'))
                <div class="alert alert-success py-2 text-sm mt-3"><span>{{ session('svc_success') }}</span></div>
            @endif

            <form wire:submit="saveService" class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-secondary">Jenis Layanan</label>
                        <select wire:model="svcType" class="select select-bordered w-full">
                            @foreach ($serviceTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('svcType') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-secondary">Meja (opsional)</label>
                        <select wire:model="svcTableId" class="select select-bordered w-full">
                            <option value="">—</option>
                            @foreach ($tables as $t)
                                <option value="{{ $t->id }}">{{ $t->code }} - {{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-secondary">Deskripsi (opsional)</label>
                    <textarea wire:model="svcDescription" maxlength="500" rows="2" class="textarea textarea-bordered w-full" placeholder="Detail layanan..."></textarea>
                </div>
                <button type="submit" class="btn btn-neutral w-full">
                    <span wire:loading.remove wire:target="saveService">Simpan Log</span>
                    <span wire:loading wire:target="saveService">Menyimpan...</span>
                </button>
            </form>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            <h3 class="font-semibold text-sm mb-3">Tip Terakhir</h3>
            <div class="space-y-2">
                @forelse ($recentTips as $tip)
                    <div class="flex items-center justify-between text-sm border-b border-base-200 pb-2">
                        <div>
                            <span class="font-semibold text-success">Rp {{ number_format((float) $tip->amount, 0, ',', '.') }}</span>
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
            <h3 class="font-semibold text-sm mb-3">Log Layanan Terakhir</h3>
            <div class="space-y-2">
                @forelse ($recentServices as $log)
                    <div class="flex items-center justify-between text-sm border-b border-base-200 pb-2">
                        <div>
                            <span class="badge badge-ghost badge-sm">{{ $serviceTypes[$log->type] ?? $log->type }}</span>
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
