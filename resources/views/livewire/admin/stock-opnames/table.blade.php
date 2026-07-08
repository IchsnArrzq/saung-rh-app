<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-xs">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari kode / catatan...">
                </div>

                <select class="select select-bordered" wire:model.live="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="posted">Diposting</option>
                </select>
            </div>

            <a href="{{ route('stock-opnames.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Buat Opname
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Jumlah Bahan</th>
                    <th>Status</th>
                    <th>Oleh</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($opnames as $opname)
                    <tr wire:key="opname-{{ $opname->id }}">
                        <td class="font-semibold text-stone-800">{{ $opname->code }}</td>
                        <td class="whitespace-nowrap text-sm text-stone-500">
                            {{ $opname->opname_date->format('d M Y') }}
                        </td>
                        <td>{{ $opname->items_count }}</td>
                        <td>
                            @if ($opname->status === 'posted')
                                <span class="badge badge-success badge-sm">Diposting</span>
                            @else
                                <span class="badge badge-warning badge-sm">Draft</span>
                            @endif
                        </td>
                        <td class="text-sm text-stone-500">{{ $opname->user?->name ?? 'Sistem' }}</td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('stock-opnames.edit', $opname) }}" class="btn btn-sm btn-outline">
                                    {{ $opname->status === 'posted' ? 'Lihat' : 'Isi Stok' }}
                                </a>
                                @unless ($opname->status === 'posted')
                                    <button type="button" class="btn btn-sm btn-error text-white"
                                        data-confirm="Hapus draft opname ini?"
                                        wire:click="delete('{{ $opname->id }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="delete('{{ $opname->id }}')">
                                        <span wire:loading.remove wire:target="delete('{{ $opname->id }}')">Hapus</span>
                                        <span wire:loading wire:target="delete('{{ $opname->id }}')" class="loading loading-spinner loading-xs"></span>
                                    </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-stone-500">Belum ada data opname.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $opnames->links() }}</div>
</div>
