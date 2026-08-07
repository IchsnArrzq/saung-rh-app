<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, kode, telepon, email..." label="Cari pelanggan" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('customers.create')">
                Tambah Pelanggan
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($customers as $customer)
            <tr wire:key="customer-row-{{ $customer->id }}">
                <td>
                    <p class="font-semibold">{{ $customer->name }}</p>
                    <p class="text-xs text-base-content/60">{{ $customer->code ?: '-' }}</p>
                </td>
                <td>{{ $customer->phone ?: '-' }}</td>
                <td>{{ $customer->email ?: '-' }}</td>
                <td>
                    <x-badge :color="$customer->is_active ? 'success' : 'ghost'">
                        {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="warning" size="sm" :href="route('customers.edit', $customer)">
                            Edit
                        </x-button>
                        <x-button variant="error" size="sm" class="text-white"
                            data-confirm="Hapus pelanggan ini?"
                            wire:click="delete('{{ $customer->id }}')"
                            loading="delete('{{ $customer->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-base-content/50">Belum ada data pelanggan.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $customers->links() }}</div>
</div>
