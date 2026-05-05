<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                        placeholder="Cari pelanggan, telp, status, meja...">
                </label>
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
                        <td>{{ $reservation->reservation_at?->format('d M Y H:i') }}</td>
                        <td><span class="badge badge-outline">{{ str_replace('_', ' ', $reservation->status) }}</span></td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <button type="button" class="btn btn-xs btn-error text-white"
                                    onclick="if (!confirm('Hapus reservasi ini?')) return false;"
                                    wire:click="delete('{{ $reservation->id }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-stone-500">Belum ada data reservasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $reservations->links() }}</div>
</div>

