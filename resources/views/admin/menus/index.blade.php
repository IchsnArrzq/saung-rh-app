<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Menu</h2>
            <a href="{{ route('menus.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Menu
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menus as $menu)
                    <tr>
                        <td>
                            <p class="font-semibold text-stone-800">{{ $menu->name }}</p>
                            <p class="text-xs text-stone-500">{{ $menu->sku ?: '-' }}</p>
                        </td>
                        <td>{{ $menu->category->name ?? '-' }}</td>
                        <td>Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $menu->is_available ? 'badge-success' : 'badge-error' }}">
                                {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('menus.edit', $menu) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <form action="{{ route('menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Hapus menu ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-stone-500">Belum ada data menu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $menus->links() }}</div>
</x-app-layout>
