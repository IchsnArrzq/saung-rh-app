<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Reservasi</h2>
            <a href="{{ route('reservations.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Reservasi
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

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
                    <tr>
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
                                <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" onsubmit="return confirm('Hapus reservasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
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

    <div class="mt-4">{{ $reservations->links() }}</div>
</x-app-layout>
