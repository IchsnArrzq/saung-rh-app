<div class="space-y-5">
    @include('admin.partials.flash')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-search-input class="sm:max-w-sm" wire:model.live.debounce.300ms="search"
            placeholder="Cari nama atau keterangan" label="Cari kategori permintaan" />

        @can('create', \App\Models\SpecialRequestCategory::class)
            <x-button variant="primary" icon="ri-add-line" :href="route('special-request-categories.create')" wire:navigate>
                Tambah kategori
            </x-button>
        @endcan
    </div>

    <div wire:loading.block wire:target="search, gotoPage, nextPage, previousPage">
        <x-skeleton :rows="5" height="h-12" />
    </div>

    <div wire:loading.remove wire:target="search, gotoPage, nextPage, previousPage" class="space-y-4">
        @if ($categories->isEmpty())
            <x-empty-state icon="ri-price-tag-3-line"
                :title="$search !== '' ? 'Tidak ada kategori yang cocok' : 'Belum ada kategori permintaan'"
                :description="$search !== ''
                    ? 'Coba kata kunci lain.'
                    : 'Kategori muncul sebagai pilihan saat tamu mengirim permintaan khusus dari panel mejanya.'">
                @if ($search !== '')
                    <x-slot:actions>
                        <x-button variant="outline" wire:click="$set('search', '')" loading="search">Hapus pencarian</x-button>
                    </x-slot:actions>
                @else
                    @can('create', \App\Models\SpecialRequestCategory::class)
                        <x-slot:actions>
                            <x-button :href="route('special-request-categories.create')" wire:navigate icon="ri-add-line">
                                Tambah kategori pertama
                            </x-button>
                        </x-slot:actions>
                    @endcan
                @endif
            </x-empty-state>
        @else
            <x-data-table>
                <x-slot:head>
                    <tr>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th class="text-right">Urutan</th>
                        <th class="text-right">Dipakai</th>
                        <th class="w-px text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($categories as $category)
                    <tr wire:key="request-category-{{ $category->id }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-base-200 text-lg"
                                    aria-hidden="true">
                                    <i class="{{ $category->iconClass() }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $category->name }}</p>
                                    @if ($category->description)
                                        <p class="max-w-md truncate text-xs text-base-content/60">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-badge size="sm" :color="$category->is_active ? 'success' : 'ghost'">
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </td>
                        <td class="text-right tabular-nums">{{ $category->sort_order }}</td>
                        <td class="text-right tabular-nums">{{ $category->special_requests_count }} permintaan</td>
                        <td>
                            <div class="flex justify-end gap-1">
                                @can('update', $category)
                                    <x-button variant="ghost" size="sm" shape="square" icon="ri-pencil-line"
                                        label="Ubah {{ $category->name }}"
                                        :href="route('special-request-categories.edit', $category)" wire:navigate />
                                @endcan
                                @can('delete', $category)
                                    <x-button variant="error" outline size="sm" shape="square" icon="ri-delete-bin-line"
                                        label="Hapus kategori {{ $category->name }}"
                                        wire:click="delete('{{ $category->id }}')" loading="delete('{{ $category->id }}')"
                                        data-confirm="Hapus kategori {{ $category->name }}?" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>

            {{ $categories->links() }}
        @endif
    </div>
</div>
