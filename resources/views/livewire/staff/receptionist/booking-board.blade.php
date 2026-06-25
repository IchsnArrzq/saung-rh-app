<div class="space-y-4">
    @if (session('success'))
        <div class="alert alert-success py-2 text-sm"><i class="ri-checkbox-circle-line"></i><span>{{ session('success') }}</span></div>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <label class="input input-bordered flex items-center gap-2 w-full sm:max-w-xs">
            <i class="ri-search-line text-secondary"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama / telp / meja..." class="grow">
        </label>
        <select wire:model.live="statusFilter" class="select select-bordered">
            <option value="all">Semua status</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="seated">Seated</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <span class="badge badge-ghost ml-auto">Hari ini: {{ $todayCount }}</span>
    </div>

    <div class="overflow-x-auto card border border-base-300 bg-base-100 rounded-xl">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Meja</th>
                    <th>Waktu</th>
                    <th>Pax</th>
                    <th>Item</th>
                    <th>DP</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $r)
                    <tr>
                        <td>
                            <div class="font-semibold">{{ $r->customer_name }}</div>
                            <div class="text-xs text-secondary">{{ $r->phone ?? '-' }}</div>
                        </td>
                        <td>{{ $r->table?->code ?? '-' }}</td>
                        <td class="text-sm">{{ $r->reservation_at?->format('d M Y H:i') ?? '-' }}</td>
                        <td>{{ $r->pax }}</td>
                        <td>{{ $r->items_count }}</td>
                        <td>
                            @if ($r->has_deposit)
                                <span class="badge badge-success badge-sm gap-1"><i class="ri-check-line"></i>Rp {{ number_format((float) $r->deposit_amount, 0, ',', '.') }}</span>
                            @elseif ($r->hold_until)
                                <span class="badge badge-warning badge-sm" title="Batas DP {{ $r->hold_until->format('d M H:i') }}">Hold {{ $r->hold_until->diffForHumans(['short' => true]) }}</span>
                            @else
                                <span class="text-xs text-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badge = match ($r->status) {
                                    'confirmed' => 'badge-info',
                                    'seated' => 'badge-success',
                                    'cancelled' => 'badge-error',
                                    default => 'badge-ghost',
                                };
                            @endphp
                            <span class="badge {{ $badge }} badge-sm">{{ str_replace('_', ' ', $r->status) }}</span>
                        </td>
                        <td>
                            <div class="flex justify-end gap-1">
                                @if (! $r->has_deposit && ! in_array($r->status, ['cancelled', 'no_show', 'completed']))
                                    <button wire:click="openDeposit('{{ $r->id }}')" class="btn btn-xs btn-outline btn-warning">Catat DP</button>
                                @endif
                                @if ($r->status !== 'confirmed' && $r->status !== 'cancelled')
                                    <button wire:click="setStatus('{{ $r->id }}', 'confirmed')" class="btn btn-xs btn-outline btn-info">Konfirmasi</button>
                                @endif
                                @if ($r->status !== 'seated' && $r->status !== 'cancelled')
                                    <button wire:click="setStatus('{{ $r->id }}', 'seated')" class="btn btn-xs btn-outline btn-success">Check-in</button>
                                @endif
                                @if ($r->status !== 'cancelled')
                                    <button wire:click="setStatus('{{ $r->id }}', 'cancelled')" data-confirm="Batalkan reservasi ini?" class="btn btn-xs btn-outline btn-error">Batal</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-8 text-secondary text-sm">Tidak ada reservasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $reservations->links() }}</div>

    {{-- Deposit (DP) form --}}
    @if ($depositFor)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:key="deposit-modal">
            <div class="card w-full max-w-sm bg-base-100 shadow-xl">
                <div class="card-body gap-4">
                    <h3 class="card-title text-base"><i class="ri-hand-coin-line text-warning"></i> Catat Uang Muka (DP)</h3>
                    <label class="form-control">
                        <span class="label-text mb-1">Nominal DP</span>
                        <input type="number" min="1" step="1000" wire:model="depositAmount" class="input input-bordered" placeholder="50000">
                        @error('depositAmount') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </label>
                    <label class="form-control">
                        <span class="label-text mb-1">Metode</span>
                        <select wire:model="depositMethod" class="select select-bordered">
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                            <option value="cash">Tunai</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="debit_card">Kartu Debit</option>
                            <option value="credit_card">Kartu Kredit</option>
                        </select>
                    </label>
                    <div class="flex justify-end gap-2">
                        <button wire:click="closeDeposit" class="btn btn-ghost btn-sm">Batal</button>
                        <button wire:click="saveDeposit" class="btn btn-warning btn-sm">Simpan DP &amp; Kunci Meja</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
