<div class="space-y-5">
    @include('admin.partials.flash')

    <div class="flex items-center justify-between gap-3">
        <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
            placeholder="Cari nama, slug, deskripsi..." label="Cari kategori menu" />

        <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('menu-categories.create')">
            Tambah Kategori
        </x-button>
    </div>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Nama</th>
                <th>Slug</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($categories as $category)
            <tr wire:key="menu-category-{{ $category->id }}">
                <td>{{ $category->name }}</td>
                <td>{{ $category->slug }}</td>
                <td>
                    <x-badge :color="$category->is_active ? 'success' : 'error'">
                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="warning" size="sm" :href="route('menu-categories.edit', $category)">
                            Edit
                        </x-button>
                        <x-button variant="error" size="sm" class="text-white"
                            data-confirm="Hapus kategori ini?"
                            wire:click="delete('{{ $category->id }}')"
                            loading="delete('{{ $category->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-base-content/50">Belum ada data kategori.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $categories->links() }}</div>
</div>
