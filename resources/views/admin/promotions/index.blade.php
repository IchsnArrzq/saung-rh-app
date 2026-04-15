<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Promo & Diskon</h2>
            <a href="{{ route('promotions.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Promo
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Nilai</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promotions as $promotion)
                    <tr>
                        <td class="font-semibold">{{ $promotion->code }}</td>
                        <td>{{ $promotion->name }}</td>
                        <td>{{ strtoupper($promotion->type) }}</td>
                        <td>{{ $promotion->value !== null ? number_format((float) $promotion->value, 0, ',', '.') : '-' }}</td>
                        <td>
                            <span class="badge {{ $promotion->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $promotion->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('promotions.edit', $promotion) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <form action="{{ route('promotions.destroy', $promotion) }}" method="POST" onsubmit="return confirm('Hapus promo ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-stone-500">Belum ada data promo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $promotions->links() }}</div>
</x-app-layout>
