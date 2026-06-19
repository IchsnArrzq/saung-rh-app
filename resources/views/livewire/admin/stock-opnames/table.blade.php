<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-xs">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari catatan...">
                </div>

                <select class="select select-bordered" wire:model.live="typeFilter">
                    <option value="">Semua Tipe</option>
                    <option value="in">Masuk</option>
                    <option value="out">Keluar</option>
                    <option value="adjustment">Koreksi</option>
                </select>

                <select class="select select-bordered" wire:model.live="ingredientFilter">
                    <option value="">Semua Bahan</option>
                    @foreach ($ingredients as $ingredient)
                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }}</option>
                    @endforeach
                </select>
            </div>

            <a href="{{ route('stock-opnames.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Input Stok
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Bahan</th>
                    <th>Tipe</th>
                    <th>Sebelum</th>
                    <th>Perubahan</th>
                    <th>Sesudah</th>
                    <th>Catatan</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr wire:key="opname-{{ $record->id }}">
                        <td class="text-sm text-stone-500 whitespace-nowrap">
                            {{ $record->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="font-medium">
                            {{ $record->ingredient->name }}
                            <span class="text-xs text-stone-400">({{ $record->ingredient->unit }})</span>
                        </td>
                        <td>
                            @if ($record->type === 'in')
                                <span class="badge badge-success badge-sm">Masuk</span>
                            @elseif ($record->type === 'out')
                                <span class="badge badge-error badge-sm">Keluar</span>
                            @else
                                <span class="badge badge-warning badge-sm">Koreksi</span>
                            @endif
                        </td>
                        <td>{{ number_format((float) $record->qty_before, 3, ',', '.') }}</td>
                        <td>
                            <span class="{{ $record->qty_change >= 0 ? 'text-success' : 'text-error' }} font-semibold">
                                {{ $record->qty_change >= 0 ? '+' : '' }}{{ number_format((float) $record->qty_change, 3, ',', '.') }}
                            </span>
                        </td>
                        <td>{{ number_format((float) $record->qty_after, 3, ',', '.') }}</td>
                        <td class="text-sm text-stone-500 max-w-xs truncate">{{ $record->notes ?: '-' }}</td>
                        <td class="text-sm text-stone-500">{{ $record->user?->name ?? 'Sistem' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-stone-500">Belum ada riwayat stok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $records->links() }}</div>
</div>
