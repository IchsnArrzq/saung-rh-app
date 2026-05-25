<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, SKU, kategori...">
                </label>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('menus.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Menu
            </a>
        </div>
    </section>

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
                    <tr wire:key="menu-row-{{ $menu->id }}">
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
                                <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    onclick="if (!confirm('Hapus menu ini?')) return false;"
                                    wire:click="delete('{{ $menu->id }}')">
                                    Hapus
                                </button>
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

    <div>{{ $menus->links() }}</div>
</div>
