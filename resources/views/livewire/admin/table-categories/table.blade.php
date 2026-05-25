<div class="space-y-5">
    @include('admin.partials.flash')

    @error('table_category')
        <div role="alert" class="alert alert-error">
            <span>{{ $message }}</span>
        </div>
    @enderror

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, slug, deskripsi...">
                </label>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('table-categories.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Kategori
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tableCategories as $tableCategory)
                    <tr wire:key="table-category-{{ $tableCategory->id }}">
                        <td>{{ $tableCategory->name }}</td>
                        <td>{{ $tableCategory->slug }}</td>
                        <td>{{ $tableCategory->sort_order }}</td>
                        <td>
                            <span class="badge {{ $tableCategory->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $tableCategory->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('table-categories.edit', $tableCategory) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    onclick="if (!confirm('Hapus kategori meja ini?')) return false;"
                                    wire:click="delete('{{ $tableCategory->id }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-stone-500">Belum ada kategori meja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $tableCategories->links() }}</div>
</div>

