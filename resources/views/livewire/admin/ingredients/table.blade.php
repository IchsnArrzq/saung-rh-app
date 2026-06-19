<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-md">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, satuan...">
                </div>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('ingredients.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Bahan
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Stok Saat Ini</th>
                    <th>Stok Minimum</th>
                    <th>Harga/Satuan</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ingredients as $ingredient)
                    <tr wire:key="ingredient-row-{{ $ingredient->id }}">
                        <td>
                            <p class="font-semibold text-stone-800">{{ $ingredient->name }}</p>
                        </td>
                        <td>{{ $ingredient->unit }}</td>
                        <td>
                            <span class="{{ $ingredient->isLowStock() ? 'text-error font-semibold' : 'text-stone-700' }}">
                                {{ number_format((float) $ingredient->stock, 3, ',', '.') }}
                            </span>
                            @if ($ingredient->isLowStock())
                                <span class="badge badge-error badge-sm ml-1">Rendah</span>
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
                            <span class="badge {{ $ingredient->is_active ? 'badge-success' : 'badge-ghost' }}">
                                {{ $ingredient->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('ingredients.edit', $ingredient) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    data-confirm="Hapus bahan ini?"
                                    wire:click="delete('{{ $ingredient->id }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-stone-500">Belum ada data bahan makanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $ingredients->links() }}</div>
</div>
