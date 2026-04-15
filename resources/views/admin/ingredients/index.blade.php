<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Bahan Baku</h2>
            <a href="{{ route('ingredients.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Bahan
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Stok Saat Ini</th>
                    <th>Minimal Stok</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ingredients as $ingredient)
                    <tr>
                        <td class="font-semibold">{{ $ingredient->name }} <span class="text-xs text-stone-500">({{ $ingredient->unit }})</span></td>
                        <td>{{ number_format((float) $ingredient->current_stock, 2, ',', '.') }}</td>
                        <td>{{ number_format((float) $ingredient->minimum_stock, 2, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $ingredient->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $ingredient->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('ingredients.edit', $ingredient) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <form action="{{ route('ingredients.destroy', $ingredient) }}" method="POST" onsubmit="return confirm('Hapus bahan baku ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-stone-500">Belum ada data bahan baku.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $ingredients->links() }}</div>
</x-app-layout>
