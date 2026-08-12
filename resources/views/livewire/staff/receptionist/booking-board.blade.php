<div class="space-y-4">
    @if (session('success'))
        <x-alert type="success" class="py-2 text-sm">{{ session('success') }}</x-alert>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <x-search-input class="w-full sm:max-w-xs" wire:model.live.debounce.300ms="search"
            placeholder="Cari nama / telp / meja..." label="Cari reservasi" />

        <x-select :bare="true" label="Filter status" wire:model.live="statusFilter" :options="[
            'all' => 'Semua status',
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'seated' => 'Sudah Duduk',
            'cancelled' => 'Dibatalkan',
        ]" />

        <x-badge color="ghost" class="ml-auto">Hari ini: {{ $todayCount }}</x-badge>
    </div>

    <x-data-table :zebra="false" size="sm">
        <x-slot:head>
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
        </x-slot:head>

        @forelse ($reservations as $r)
            <tr>
                <td>
                    <div class="font-semibold">{{ $r->customer_name }}</div>
                    <div class="text-xs text-base-content/60">{{ $r->phone ?? '-' }}</div>
                </td>
                <td>{{ $r->table?->code ?? '-' }}</td>
                <td class="text-sm">{{ $r->reservation_at?->format('d M Y H:i') ?? '-' }}</td>
                <td>{{ $r->pax }}</td>
                <td>{{ $r->items_count }}</td>
                <td>
                    @if ($r->has_deposit)
                        <x-badge color="success" size="sm" icon="ri-check-line" class="gap-1">
                            Rp {{ number_format((float) $r->deposit_amount, 0, ',', '.') }}
                        </x-badge>
                    @elseif ($r->hold_until)
                        <x-badge color="warning" size="sm" title="Batas DP {{ $r->hold_until->format('d M H:i') }}">
                            Hold {{ $r->hold_until->diffForHumans(['short' => true]) }}
                        </x-badge>
                    @else
                        <span class="text-xs text-base-content/60">-</span>
                    @endif
                </td>
                <td>
                    <x-status-badge :status="$r->status" size="sm"
                        :enum="\App\Domains\Reservation\Enums\ReservationStatus::class" />
                </td>
                <td>
                    <div class="flex justify-end gap-1">
                        @if (! $r->has_deposit && ! in_array($r->status, ['cancelled', 'no_show', 'completed']))
                            <x-button variant="warning" :outline="true" size="xs"
                                wire:click="openDeposit('{{ $r->id }}')">
                                Catat DP
                            </x-button>
                        @endif
                        @if ($r->status !== 'confirmed' && $r->status !== 'cancelled')
                            <x-button variant="info" :outline="true" size="xs"
                                wire:click="setStatus('{{ $r->id }}', 'confirmed')">
                                Konfirmasi
                            </x-button>
                        @endif
                        @if ($r->status !== 'seated' && $r->status !== 'cancelled')
                            <x-button variant="success" :outline="true" size="xs"
                                wire:click="setStatus('{{ $r->id }}', 'seated')">
                                Check-in
                            </x-button>
                        @endif
                        @if ($r->status !== 'cancelled')
                            <x-button variant="error" :outline="true" size="xs"
                                wire:click="setStatus('{{ $r->id }}', 'cancelled')"
                                data-confirm="Batalkan reservasi ini?">
                                Batal
                            </x-button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="py-8 text-center text-sm text-base-content/50">Tidak ada reservasi.</td></tr>
        @endforelse
    </x-data-table>

    <div>{{ $reservations->links() }}</div>

    {{-- Deposit (DP) form --}}
    @if ($depositFor)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:key="deposit-modal">
            <div class="card w-full max-w-sm bg-base-100 shadow-xl">
                <div class="card-body gap-4">
                    <h3 class="card-title text-base"><i class="ri-hand-coin-line text-warning"></i> Catat Uang Muka (DP)</h3>

                    <x-input label="Nominal DP" name="depositAmount" type="number" min="1" step="1000"
                        wire:model="depositAmount" placeholder="50000" />

                    <x-select label="Metode" name="depositMethod" wire:model="depositMethod" :options="[
                        'transfer' => 'Transfer',
                        'qris' => 'QRIS',
                        'cash' => 'Tunai',
                        'ewallet' => 'E-Wallet',
                        'debit_card' => 'Kartu Debit',
                        'credit_card' => 'Kartu Kredit',
                    ]" />

                    <div class="flex justify-end gap-2">
                        <x-button variant="ghost" size="sm" wire:click="closeDeposit">Batal</x-button>
                        <x-button variant="warning" size="sm" wire:click="saveDeposit" loading="saveDeposit">
                            Simpan DP &amp; Kunci Meja
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
