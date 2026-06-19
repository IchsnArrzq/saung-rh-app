<div class="space-y-5">
    @include('admin.partials.flash')

    @error('reservation')
        <div role="alert" class="alert alert-error">
            <span>{{ $message }}</span>
        </div>
    @enderror

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-md">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari pelanggan, telp, status, meja...">
                </div>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('reservations.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Reservasi
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Meja</th>
                    <th>Pax</th>
                    <th>Item</th>
                    <th>Jadwal</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    <tr wire:key="reservation-{{ $reservation->id }}">
                        <td>
                            <p class="font-semibold">{{ $reservation->customer_name }}</p>
                            <p class="text-xs text-stone-500">{{ $reservation->phone ?: '-' }}</p>
                        </td>
                        <td>{{ $reservation->table->code ?? '-' }}</td>
                        <td>{{ $reservation->pax }}</td>
                        <td>{{ $reservation->items_count }}</td>
                        <td>{{ $reservation->reservation_at?->format('d M Y H:i') }}</td>
                        <td><span class="badge badge-outline">{{ str_replace('_', ' ', $reservation->status) }}</span></td>
                        <td class="text-right">
                            <div class="inline-flex flex-wrap justify-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline"
                                    wire:click="showDetail('{{ $reservation->id }}')">
                                    Detail
                                </button>
                                <button type="button" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700"
                                    data-confirm="Generate order dan order item dari reservasi ini?"
                                    data-confirm-title="Generate Order"
                                    data-confirm-yes="Ya, Generate"
                                    wire:click="generateOrder('{{ $reservation->id }}')">
                                    Generate Order
                                </button>
                                <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    data-confirm="Hapus reservasi ini?"
                                    wire:click="delete('{{ $reservation->id }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-stone-500">Belum ada data reservasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $reservations->links() }}</div>

    <x-modal name="reservation-detail-modal" maxWidth="4xl">
        @if ($selectedReservation)
            <div class="space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-semibold text-stone-900">Detail Reservasi</h3>
                        <p class="mt-1 text-sm text-stone-500">{{ $selectedReservation['customer_name'] }} - {{ $selectedReservation['reservation_at'] }}</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-ghost btn-circle" x-on:click="$dispatch('close')">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>

                <div class="grid gap-3 md:grid-cols-4">
                    <div class="rounded-xl border border-stone-200 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Pelanggan</p>
                        <p class="mt-1 font-semibold text-stone-900">{{ $selectedReservation['customer_name'] }}</p>
                        <p class="text-sm text-stone-500">{{ $selectedReservation['phone'] }}</p>
                    </div>
                    <div class="rounded-xl border border-stone-200 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Meja</p>
                        <p class="mt-1 font-semibold text-stone-900">{{ $selectedReservation['table'] }}</p>
                        <p class="text-sm text-stone-500">{{ $selectedReservation['pax'] }} pax</p>
                    </div>
                    <div class="rounded-xl border border-stone-200 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Status</p>
                        <p class="mt-1 font-semibold capitalize text-stone-900">{{ $selectedReservation['status'] }}</p>
                    </div>
                    <div class="rounded-xl border border-stone-200 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Estimasi</p>
                        <p class="mt-1 font-semibold text-emerald-800">Rp {{ number_format((float) $selectedReservation['subtotal'], 0, ',', '.') }}</p>
                    </div>
                </div>

                @if ($selectedReservation['notes'] !== '')
                    <div class="rounded-xl border border-stone-200 bg-stone-50 p-3 text-sm text-stone-700">
                        {{ $selectedReservation['notes'] }}
                    </div>
                @endif

                <div class="overflow-x-auto rounded-2xl border border-stone-200">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                    <td colspan="5" class="text-center text-stone-500">Belum ada item reservasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-modal>
</div>
