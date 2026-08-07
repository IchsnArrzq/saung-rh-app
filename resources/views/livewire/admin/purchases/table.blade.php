<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-xs" wire:model.live.debounce.300ms="search"
                    placeholder="Cari kode / supplier / catatan..." label="Cari pembelian" />

                <x-select :bare="true" label="Filter status" wire:model.live="statusFilter"
                    placeholder="Semua Status" :options="['draft' => 'Draft', 'posted' => 'Diposting']" />
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('purchases.create')">
                Buat Pembelian
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th class="text-right">Total</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($purchases as $purchase)
            <tr wire:key="purchase-{{ $purchase->id }}">
                <td class="font-semibold">{{ $purchase->code }}</td>
                <td class="whitespace-nowrap text-sm text-base-content/60">{{ $purchase->purchase_date->format('d M Y') }}</td>
                <td>{{ $purchase->supplier?->name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format((float) $purchase->total, 0, ',', '.') }}</td>
                <td>
                    <x-badge :color="$purchase->status === 'posted' ? 'success' : 'warning'" size="sm">
                        {{ $purchase->status === 'posted' ? 'Diposting' : 'Draft' }}
                    </x-badge>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="outline" size="sm" :href="route('purchases.edit', $purchase)">
                            {{ $purchase->status === 'posted' ? 'Lihat' : 'Edit' }}
                        </x-button>

                        @unless ($purchase->status === 'posted')
                            <x-button variant="error" size="sm" class="text-white"
                                data-confirm="Hapus draft pembelian ini?"
                                wire:click="delete('{{ $purchase->id }}')"
                                loading="delete('{{ $purchase->id }}')">
                                Hapus
                            </x-button>
                        @endunless
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-base-content/50">Belum ada data pembelian.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $purchases->links() }}</div>
</div>
