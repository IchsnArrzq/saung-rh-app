<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-xs" wire:model.live.debounce.300ms="search"
                    placeholder="Cari kode / pelanggan / catatan..." label="Cari penjualan" />

                <x-select :bare="true" class="w-full max-w-64" label="Filter status" wire:model.live="statusFilter"
                    placeholder="Semua Status" :options="['draft' => 'Draft', 'posted' => 'Diposting']" />
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('sales.create')">
                Buat Penjualan
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th class="text-right">Total</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($sales as $sale)
            <tr wire:key="sale-{{ $sale->id }}">
                <td class="font-semibold">{{ $sale->code }}</td>
                <td class="whitespace-nowrap text-sm text-base-content/60">{{ $sale->sale_date->format('d M Y') }}</td>
                <td>{{ $sale->customer?->name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</td>
                <td>
                    <x-badge :color="$sale->status === 'posted' ? 'success' : 'warning'" size="sm">
                        {{ $sale->status === 'posted' ? 'Diposting' : 'Draft' }}
                    </x-badge>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="outline" size="sm" :href="route('sales.edit', $sale)">
                            {{ $sale->status === 'posted' ? 'Lihat' : 'Edit' }}
                        </x-button>

                        @unless ($sale->status === 'posted')
                            <x-button variant="error" size="sm" class="text-white"
                                data-confirm="Hapus draft penjualan ini?"
                                wire:click="delete('{{ $sale->id }}')"
                                loading="delete('{{ $sale->id }}')">
                                Hapus
                            </x-button>
                        @endunless
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-base-content/50">Belum ada data penjualan.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $sales->links() }}</div>
</div>
