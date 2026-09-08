<div class="space-y-5">
    @include('admin.partials.flash')

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card title="Total bahan aktif" :value="number_format((float) $totalItems, 0, ',', '.')" />

        <x-stat-card title="Stok rendah" :value="number_format((float) $lowCount, 0, ',', '.')"
            :color="$lowCount > 0 ? 'error' : null" />

        <x-stat-card title="Nilai persediaan" :value="'Rp ' . number_format((float) $totalValue, 0, ',', '.')" />
    </div>

    <x-card>
        <div class="flex flex-wrap items-center gap-3">
            <x-search-input class="max-w-xs" placeholder="Cari bahan..."
                wire:model.live.debounce.300ms="search" />

            {{-- check-ui-allow: kotak centang mengirim satu event `change`, tidak ada ketukan untuk di-debounce. --}}
            <x-checkbox size="sm" label="Hanya stok rendah" wire:model.live="lowOnly" value="1" />
        </div>
    </x-card>

    @if ($ingredients->isEmpty())
        {{-- Empty state berdiri sendiri, bukan di dalam <td>: satu kotak, satu gagasan. --}}
        <x-empty-state icon="ri-archive-line"
            :title="$search !== '' || $lowOnly !== '' ? 'Tidak ada bahan yang cocok' : 'Belum ada bahan'"
            :description="$search !== '' || $lowOnly !== ''
                ? 'Coba ubah kata kunci atau matikan filter stok rendah.'
                : 'Bahan yang kamu tambahkan akan muncul di sini beserta nilai persediaannya.'">
            <x-slot:actions>
                @if ($search === '' && $lowOnly === '')
                    <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('ingredients.create')"
                        wire:navigate>Tambah bahan</x-button>
                @endif
            </x-slot:actions>
        </x-empty-state>
    @else
    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Bahan</th>
                <th>Satuan</th>
                <th class="text-right">Stok</th>
                <th class="text-right">Min</th>
                <th class="text-right">Harga/satuan</th>
                <th class="text-right">Nilai</th>
                <th>Status</th>
            </tr>
        </x-slot:head>

        @foreach ($ingredients as $ingredient)
            @php($value = (float) $ingredient->stock * (float) ($ingredient->cost_per_unit ?? 0))
            <tr wire:key="stock-{{ $ingredient->id }}">
                <td class="font-medium">{{ $ingredient->name }}</td>
                <td>{{ $ingredient->unit }}</td>
                <td class="text-right tabular-nums {{ $ingredient->isLowStock() ? 'font-semibold text-error' : '' }}">
                    {{ number_format((float) $ingredient->stock, 3, ',', '.') }}
                </td>
                <td class="text-right tabular-nums">{{ number_format((float) $ingredient->min_stock, 3, ',', '.') }}</td>
                <td class="text-right tabular-nums">
                    {{ $ingredient->cost_per_unit ? 'Rp '.number_format((float) $ingredient->cost_per_unit, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right tabular-nums">Rp {{ number_format($value, 0, ',', '.') }}</td>
                <td>
                    @if ($ingredient->isLowStock())
                        <x-badge color="error" size="sm">Rendah</x-badge>
                    @else
                        <x-badge color="success" size="sm">Aman</x-badge>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
    @endif

    <div>{{ $ingredients->links() }}</div>
</div>
