<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, kode, kontak, telepon..." label="Cari supplier" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('suppliers.create')">
                Tambah Supplier
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Nama</th>
                <th>Kontak</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($suppliers as $supplier)
            <tr wire:key="supplier-row-{{ $supplier->id }}">
                <td>
                    <p class="font-semibold">{{ $supplier->name }}</p>
                    <p class="text-xs text-base-content/60">{{ $supplier->code ?: '-' }}</p>
                </td>
                <td>{{ $supplier->contact_person ?: '-' }}</td>
                <td>{{ $supplier->phone ?: '-' }}</td>
                <td>{{ $supplier->email ?: '-' }}</td>
                <td>
                    <x-badge :color="$supplier->is_active ? 'success' : 'ghost'">
                        {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="warning" size="sm" :href="route('suppliers.edit', $supplier)">
                            Edit
                        </x-button>
                        <x-button variant="error" size="sm" class="text-white"
                            data-confirm="Hapus supplier ini?"
                            wire:click="delete('{{ $supplier->id }}')"
                            loading="delete('{{ $supplier->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-base-content/50">Belum ada data supplier.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $suppliers->links() }}</div>
</div>
