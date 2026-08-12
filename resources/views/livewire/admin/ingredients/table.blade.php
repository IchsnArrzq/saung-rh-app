<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, satuan..." label="Cari bahan" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('ingredients.create')">
                Tambah Bahan
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Nama Bahan</th>
                <th>Satuan</th>
                <th>Stok Saat Ini</th>
                <th>Stok Minimum</th>
                <th>Harga/Satuan</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($ingredients as $ingredient)
            <tr wire:key="ingredient-row-{{ $ingredient->id }}">
                <td>
                    <p class="font-semibold">{{ $ingredient->name }}</p>
                </td>
                <td>{{ $ingredient->unit }}</td>
                <td>
                    <span class="{{ $ingredient->isLowStock() ? 'font-semibold text-error' : 'text-base-content/80' }}">
                        {{ number_format((float) $ingredient->stock, 3, ',', '.') }}
                    </span>
                    @if ($ingredient->isLowStock())
                        <x-badge color="error" size="sm" class="ml-1">Rendah</x-badge>
                    @endif
                </td>
                <td>{{ number_format((float) $ingredient->min_stock, 3, ',', '.') }}</td>
                <td>
                    @if ($ingredient->cost_per_unit)
                        Rp {{ number_format((float) $ingredient->cost_per_unit, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    <x-badge :color="$ingredient->is_active ? 'success' : 'ghost'">
                        {{ $ingredient->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="warning" size="sm" :href="route('ingredients.edit', $ingredient)">
                            Edit
                        </x-button>
                        <x-button variant="error" size="sm" class="text-white"
                            data-confirm="Hapus bahan ini?"
                            wire:click="delete('{{ $ingredient->id }}')"
                            loading="delete('{{ $ingredient->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-base-content/50">Belum ada data bahan makanan.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $ingredients->links() }}</div>
</div>
