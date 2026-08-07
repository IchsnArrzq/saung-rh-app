<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, SKU, kategori..." label="Cari menu" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('menus.create')">
                Tambah Menu
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($menus as $menu)
            <tr wire:key="menu-row-{{ $menu->id }}">
                <td>
                    <p class="font-semibold">{{ $menu->name }}</p>
                    <p class="text-xs text-base-content/60">{{ $menu->sku ?: '-' }}</p>
                </td>
                <td>{{ $menu->category->name ?? '-' }}</td>
                <td>Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</td>
                <td>
                    <x-badge :color="$menu->is_available ? 'success' : 'error'">
                        {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                    </x-badge>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="outline" size="sm" :href="route('public.menu.show', $menu)"
                            target="_blank" rel="noopener">
                            Detail
                        </x-button>
                        <x-button variant="info" size="sm" icon="ri-flask-line" class="text-white"
                            :href="route('menus.ingredients.edit', $menu)" title="Resep / Bahan">
                            Resep
                        </x-button>
                        <x-button variant="neutral" size="sm" icon="ri-image-line" class="text-white"
                            :href="route('menus.media.edit', $menu)" title="Gambar & Video">
                            Media
                        </x-button>
                        <x-button variant="warning" size="sm" :href="route('menus.edit', $menu)">Edit</x-button>
                        <x-button variant="error" size="sm" class="text-white"
                            data-confirm="Hapus menu ini?"
                            wire:click="delete('{{ $menu->id }}')"
                            loading="delete('{{ $menu->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-base-content/50">Belum ada data menu.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $menus->links() }}</div>
</div>
