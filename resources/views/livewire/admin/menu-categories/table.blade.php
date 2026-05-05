<div class="space-y-5">
    @include('admin.partials.flash')

    <div class="flex items-center justify-between gap-3">
        <label class="input input-bordered flex w-full max-w-md items-center gap-2">
            <i class="ri-search-line text-stone-400"></i>
            <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                placeholder="Cari nama, slug, deskripsi...">
        </label>

        <a href="{{ route('menu-categories.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
            <i class="ri-add-line"></i>
            Tambah Kategori
        </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr wire:key="menu-category-{{ $category->id }}">
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('menu-categories.edit', $category) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <button type="button" class="btn btn-xs btn-error text-white"
                                    onclick="if (!confirm('Hapus kategori ini?')) return false;"
                                    wire:click="delete('{{ $category->id }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-stone-500">Belum ada data kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $categories->links() }}</div>
</div>

