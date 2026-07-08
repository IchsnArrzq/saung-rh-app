<div class="space-y-5">
    @include('admin.partials.flash')

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-stone-200 bg-white p-4">
            <p class="text-sm text-stone-500">Total Bahan Aktif</p>
            <p class="mt-1 text-2xl font-bold text-stone-800">{{ number_format($totalItems, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-4">
            <p class="text-sm text-stone-500">Stok Rendah</p>
            <p class="mt-1 text-2xl font-bold {{ $lowCount > 0 ? 'text-error' : 'text-stone-800' }}">
                {{ number_format($lowCount, 0, ',', '.') }}
            </p>
        </div>
        <div class="rounded-2xl border border-stone-200 bg-white p-4">
            <p class="text-sm text-stone-500">Nilai Persediaan</p>
            <p class="mt-1 text-2xl font-bold text-emerald-800">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
        </div>
    </div>

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-xs">
                <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                    placeholder="Cari bahan...">
            </div>
            <label class="label cursor-pointer gap-2">
                <input type="checkbox" class="checkbox checkbox-sm" wire:model.live="lowOnly" value="1">
                <span class="label-text">Hanya stok rendah</span>
            </label>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Bahan</th>
                    <th>Satuan</th>
                    <th class="text-right">Stok</th>
                    <th class="text-right">Min</th>
                    <th class="text-right">Harga/Satuan</th>
                    <th class="text-right">Nilai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ingredients as $ingredient)
                    @php($value = (float) $ingredient->stock * (float) ($ingredient->cost_per_unit ?? 0))
                    <tr wire:key="stock-{{ $ingredient->id }}">
                        <td class="font-medium text-stone-800">{{ $ingredient->name }}</td>
                        <td>{{ $ingredient->unit }}</td>
                        <td class="text-right {{ $ingredient->isLowStock() ? 'text-error font-semibold' : '' }}">
                            {{ number_format((float) $ingredient->stock, 3, ',', '.') }}
                        </td>
                        <td class="text-right">{{ number_format((float) $ingredient->min_stock, 3, ',', '.') }}</td>
                        <td class="text-right">
                            {{ $ingredient->cost_per_unit ? 'Rp '.number_format((float) $ingredient->cost_per_unit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">Rp {{ number_format($value, 0, ',', '.') }}</td>
                        <td>
                            @if ($ingredient->isLowStock())
                                <span class="badge badge-error badge-sm">Rendah</span>
                            @else
                                <span class="badge badge-success badge-sm">Aman</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-stone-500">Tidak ada bahan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $ingredients->links() }}</div>
</div>
