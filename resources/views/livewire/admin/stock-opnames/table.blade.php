<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-xs" wire:model.live.debounce.300ms="search"
                    placeholder="Cari kode / catatan..." label="Cari opname" />

                <x-select :bare="true" class="w-full max-w-64" label="Filter status" wire:model.live="statusFilter"
                    placeholder="Semua Status" :options="['draft' => 'Draft', 'posted' => 'Diposting']" />
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('stock-opnames.create')">
                Buat Opname
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Jumlah Bahan</th>
                <th>Status</th>
                <th>Oleh</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($opnames as $opname)
            <tr wire:key="opname-{{ $opname->id }}">
                <td class="font-semibold">{{ $opname->code }}</td>
                <td class="whitespace-nowrap text-sm text-base-content/60">
                    {{ $opname->opname_date->format('d M Y') }}
                </td>
                <td>{{ $opname->items_count }}</td>
                <td>
                    <x-badge :color="$opname->status === 'posted' ? 'success' : 'warning'" size="sm">
                        {{ $opname->status === 'posted' ? 'Diposting' : 'Draft' }}
                    </x-badge>
                </td>
                <td class="text-sm text-base-content/60">{{ $opname->user?->name ?? 'Sistem' }}</td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="outline" size="sm" :href="route('stock-opnames.edit', $opname)">
                            {{ $opname->status === 'posted' ? 'Lihat' : 'Isi Stok' }}
                        </x-button>

                        @unless ($opname->status === 'posted')
                            <x-button variant="error" size="sm" class="text-white"
                                data-confirm="Hapus draft opname ini?"
                                wire:click="delete('{{ $opname->id }}')"
                                loading="delete('{{ $opname->id }}')">
                                Hapus
                            </x-button>
                        @endunless
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-base-content/50">Belum ada data opname.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $opnames->links() }}</div>
</div>
