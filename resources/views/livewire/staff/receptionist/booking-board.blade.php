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
                    <tr><td colspan="7" class="text-center py-8 text-secondary text-sm">Tidak ada reservasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $reservations->links() }}</div>
</div>
