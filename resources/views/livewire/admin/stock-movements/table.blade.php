<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center gap-3">
            <x-search-input class="max-w-xs" placeholder="Cari catatan / bahan..."
                wire:model.live.debounce.300ms="search" />

            {{-- check-ui-allow: select mengirim satu event `change` — debounce hanya menunda hasil filter. --}}
            <x-select wire:model.live="typeFilter" :bare="true" class="w-full max-w-64" label="Tipe" name="typeFilter"
                placeholder="Semua tipe" :options="['in' => 'Masuk', 'out' => 'Keluar', 'adjustment' => 'Koreksi']" />

            {{-- check-ui-allow: sama seperti filter tipe — pilihan, bukan ketikan. --}}
            <x-select wire:model.live="ingredientFilter" :bare="true" class="w-full max-w-64" label="Bahan"
                name="ingredientFilter" placeholder="Semua bahan" :options="$ingredients->pluck('name', 'id')->all()" />
        </div>
    </x-card>

    @if ($records->isEmpty())
        <x-empty-state icon="ri-history-line"
            :title="$search !== '' || $typeFilter !== '' || $ingredientFilter !== '' ? 'Tidak ada riwayat yang cocok' : 'Belum ada riwayat stok'"
            :description="$search !== '' || $typeFilter !== '' || $ingredientFilter !== ''
                ? 'Coba ubah kata kunci atau kosongkan filter tipe dan bahan.'
                : 'Setiap pembelian, pemakaian, dan koreksi stok akan tercatat di sini.'" />
    @else
        <x-data-table>
            <x-slot:head>
                <tr>
                    <th>Tanggal</th>
                    <th>Bahan</th>
                    <th>Tipe</th>
                    <th class="text-right">Sebelum</th>
                    <th class="text-right">Perubahan</th>
                    <th class="text-right">Sesudah</th>
                    <th>Catatan</th>
                    <th>Oleh</th>
                </tr>
            </x-slot:head>

            @foreach ($records as $record)
                <tr wire:key="movement-{{ $record->id }}">
                    <td class="whitespace-nowrap text-sm tabular-nums text-base-content/60">
                        {{ $record->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="font-medium">
                        {{ $record->ingredient?->name ?? '-' }}
                        <span class="text-xs text-base-content/60">({{ $record->ingredient?->unit }})</span>
                    </td>
                    <td>
                        @if ($record->type === 'in')
                            <x-badge color="success" size="sm">Masuk</x-badge>
                        @elseif ($record->type === 'out')
                            <x-badge color="error" size="sm">Keluar</x-badge>
                        @else
                            <x-badge color="warning" size="sm">Koreksi</x-badge>
                        @endif
                    </td>
                    <td class="text-right tabular-nums">{{ number_format((float) $record->qty_before, 3, ',', '.') }}</td>
                    <td class="text-right tabular-nums">
                        <span class="font-semibold {{ $record->qty_change >= 0 ? 'text-success' : 'text-error' }}">
                            {{ $record->qty_change >= 0 ? '+' : '' }}{{ number_format((float) $record->qty_change, 3, ',', '.') }}
                        </span>
                    </td>
                    <td class="text-right tabular-nums">{{ number_format((float) $record->qty_after, 3, ',', '.') }}</td>
                    <td class="max-w-xs truncate text-sm text-base-content/70">{{ $record->notes ?: '-' }}</td>
                    <td class="text-sm text-base-content/70">{{ $record->user?->name ?? 'Sistem' }}</td>
                </tr>
            @endforeach
        </x-data-table>
    @endif

    <div>{{ $records->links() }}</div>
</div>
