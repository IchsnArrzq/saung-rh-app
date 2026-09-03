<div class="space-y-5">
    @include('admin.partials.flash')

    @error('table_category')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, slug, deskripsi..." label="Cari kategori meja" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            @can('create', App\Models\TableCategory::class)
                <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('table-categories.create')">
                    Tambah Kategori
                </x-button>
            @endcan
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Nama</th>
                <th>Slug</th>
                <th>Urutan</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($tableCategories as $tableCategory)
            <tr wire:key="table-category-{{ $tableCategory->id }}">
                <td>{{ $tableCategory->name }}</td>
                <td>{{ $tableCategory->slug }}</td>
                <td>{{ $tableCategory->sort_order }}</td>
                <td>
                    <x-badge :color="$tableCategory->is_active ? 'success' : 'error'">
                        {{ $tableCategory->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        @can('update', $tableCategory)
                            <x-button variant="warning" size="sm" :href="route('table-categories.edit', $tableCategory)">
                                Edit
                            </x-button>
                        @endcan

                        @can('delete', $tableCategory)
                            <x-button variant="error" size="sm" class="text-white"
                                data-confirm="Hapus kategori meja ini?"
                                wire:click="delete('{{ $tableCategory->id }}')"
                                loading="delete('{{ $tableCategory->id }}')">
                                Hapus
                            </x-button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-base-content/50">Belum ada kategori meja.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $tableCategories->links() }}</div>
</div>
