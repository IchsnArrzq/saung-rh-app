<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-xs">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari kode / supplier / catatan...">
                </div>

                <select class="select select-bordered" wire:model.live="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="posted">Diposting</option>
                </select>
            </div>

            <a href="{{ route('purchases.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Buat Pembelian
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th class="text-right">Total</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr wire:key="purchase-{{ $purchase->id }}">
                        <td class="font-semibold text-stone-800">{{ $purchase->code }}</td>
                        <td class="whitespace-nowrap text-sm text-stone-500">{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td>{{ $purchase->supplier?->name ?? '-' }}</td>
                        <td class="text-right">Rp {{ number_format((float) $purchase->total, 0, ',', '.') }}</td>
                        <td>
                            @if ($purchase->status === 'posted')
                                <span class="badge badge-success badge-sm">Diposting</span>
                            @else
                                <span class="badge badge-warning badge-sm">Draft</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-outline">
                                    {{ $purchase->status === 'posted' ? 'Lihat' : 'Edit' }}
                                </a>
                                @unless ($purchase->status === 'posted')
                                    <button type="button" class="btn btn-sm btn-error text-white"
                                        data-confirm="Hapus draft pembelian ini?"
                                        wire:click="delete('{{ $purchase->id }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="delete('{{ $purchase->id }}')">
                                        <span wire:loading.remove wire:target="delete('{{ $purchase->id }}')">Hapus</span>
                                        <span wire:loading wire:target="delete('{{ $purchase->id }}')" class="loading loading-spinner loading-xs"></span>
                                    </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-stone-500">Belum ada data pembelian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $purchases->links() }}</div>
</div>
