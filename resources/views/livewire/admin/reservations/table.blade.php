<div class="space-y-5">
    @include('admin.partials.flash')

    @error('reservation')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari pelanggan, telp, status, meja..." label="Cari reservasi" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('reservations.create')">
                Tambah Reservasi
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Pelanggan</th>
                <th>Meja</th>
                <th>Pax</th>
                <th>Item</th>
                <th>Jadwal</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($reservations as $reservation)
            <tr wire:key="reservation-{{ $reservation->id }}">
                <td>
                    <p class="font-semibold">{{ $reservation->customer_name }}</p>
                    <p class="text-xs text-base-content/60">{{ $reservation->phone ?: '-' }}</p>
                </td>
                <td>{{ $reservation->table->code ?? '-' }}</td>
                <td>{{ $reservation->pax }}</td>
                <td>{{ $reservation->items_count }}</td>
                <td>{{ $reservation->reservation_at?->format('d M Y H:i') }}</td>
                <td>
                    <x-status-badge :status="$reservation->status"
                        :enum="\App\Domains\Reservation\Enums\ReservationStatus::class" />
                </td>
                <td class="text-right">
                    <div class="inline-flex flex-wrap justify-end gap-2">
                        <x-button variant="outline" size="sm" wire:click="showDetail('{{ $reservation->id }}')">
                            Detail
                        </x-button>
                        <x-button variant="accent" size="sm"
                            data-confirm="Generate order dan order item dari reservasi ini?"
                            data-confirm-title="Generate Order"
                            data-confirm-yes="Ya, Generate"
                            wire:click="generateOrder('{{ $reservation->id }}')"
                            loading="generateOrder('{{ $reservation->id }}')">
                            Generate Order
                        </x-button>
                        <x-button variant="warning" size="sm" :href="route('reservations.edit', $reservation)">
                            Edit
                        </x-button>
                        <x-button variant="error" size="sm" class="text-white"
                            data-confirm="Hapus reservasi ini?"
                            wire:click="delete('{{ $reservation->id }}')"
                            loading="delete('{{ $reservation->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-base-content/50">Belum ada data reservasi.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $reservations->links() }}</div>

    <x-modal name="reservation-detail-modal" maxWidth="4xl">
        @if ($selectedReservation)
            <div class="space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-semibold">Detail Reservasi</h3>
                        <p class="mt-1 text-sm text-base-content/60">{{ $selectedReservation['customer_name'] }} - {{ $selectedReservation['reservation_at'] }}</p>
                    </div>
                    <x-button variant="ghost" size="sm" shape="circle" icon="ri-close-line text-lg"
                        label="Tutup" x-on:click="$dispatch('close')" />
                </div>

                <div class="grid gap-3 md:grid-cols-4">
                    <div class="rounded-xl border border-base-300 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pelanggan</p>
                        <p class="mt-1 font-semibold">{{ $selectedReservation['customer_name'] }}</p>
                        <p class="text-sm text-base-content/60">{{ $selectedReservation['phone'] }}</p>
                    </div>
                    <div class="rounded-xl border border-base-300 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Meja</p>
                        <p class="mt-1 font-semibold">{{ $selectedReservation['table'] }}</p>
                        <p class="text-sm text-base-content/60">{{ $selectedReservation['pax'] }} pax</p>
                    </div>
                    <div class="rounded-xl border border-base-300 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Status</p>
                        <div class="mt-1">
                            <x-status-badge :status="$selectedReservation['status']"
                                :enum="\App\Domains\Reservation\Enums\ReservationStatus::class" />
                        </div>
                    </div>
                    <div class="rounded-xl border border-base-300 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Estimasi</p>
                        <p class="mt-1 font-semibold text-accent">Rp {{ number_format((float) $selectedReservation['subtotal'], 0, ',', '.') }}</p>
                    </div>
                </div>

                @if ($selectedReservation['notes'] !== '')
                    <div class="rounded-xl border border-base-300 bg-base-200 p-3 text-sm text-base-content/80">
                        {{ $selectedReservation['notes'] }}
                    </div>
                @endif

                <x-data-table :zebra="false">
                    <x-slot:head>
                        <tr>
                            <th>Menu</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Catatan</th>
                        </tr>
                    </x-slot:head>

                    @forelse ($selectedReservation['items'] as $item)
                        <tr>
                            <td class="font-semibold">{{ $item['name'] }}</td>
                            <td>{{ $item['qty'] }}</td>
                            <td>Rp {{ number_format((float) $item['unit_price'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format((float) $item['line_total'], 0, ',', '.') }}</td>
                            <td>{{ $item['notes'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/50">Belum ada item reservasi.</td>
                        </tr>
                    @endforelse
                </x-data-table>
            </div>
        @endif
    </x-modal>
</div>
