<div class="space-y-5">
    @include('admin.partials.flash')

    @php($posted = $purchase && $purchase->status === 'posted')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 rounded-2xl border border-stone-200 bg-white p-4 md:grid-cols-3 md:p-5">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Supplier</legend>
                <select class="select select-bordered w-full" wire:model.defer="supplier_id" @disabled($posted)>
                    <option value="">Tanpa supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tanggal</legend>
                <input type="date" class="input input-bordered w-full" wire:model.defer="purchase_date" @disabled($posted)>
                @error('purchase_date')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Catatan</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="notes" @disabled($posted)>
                @error('notes')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-2/5">Bahan</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-right">Subtotal</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @php($total = 0)
                    @forelse ($items as $index => $row)
                        @php($subtotal = (float) ($row['qty'] ?: 0) * (float) ($row['unit_cost'] ?: 0))
                        @php($total += $subtotal)
                        <tr wire:key="purchase-item-{{ $index }}">
                            <td>
                                <select class="select select-bordered select-sm w-full"
                                    wire:model="items.{{ $index }}.ingredient_id" @disabled($posted)>
                                    <option value="">-- Pilih Bahan --</option>
                                    @foreach ($ingredients as $ingredient)
                                        <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit }})</option>
                                    @endforeach
                                </select>
                                @error("items.{$index}.ingredient_id")
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0.001"
                                    class="input input-bordered input-sm w-28 text-right"
                                    wire:model.live.debounce.400ms="items.{{ $index }}.qty" @disabled($posted)>
                                @error("items.{$index}.qty")
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0"
                                    class="input input-bordered input-sm w-32 text-right"
                                    wire:model.live.debounce.400ms="items.{{ $index }}.unit_cost" @disabled($posted)>
                                @error("items.{$index}.unit_cost")
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="text-right whitespace-nowrap">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            <td>
                                @unless ($posted)
                                    <button type="button" class="btn btn-sm btn-ghost text-error"
                                        wire:click="removeRow({{ $index }})">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-stone-400 py-6">Belum ada item.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Total</th>
                        <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        @error('items')
            <p class="text-error text-sm">{{ $message }}</p>
        @enderror

        <div class="flex flex-wrap items-center justify-between gap-2">
            @unless ($posted)
                <button type="button" class="btn btn-sm btn-outline" wire:click="addRow">
                    <i class="ri-add-line"></i> Tambah Item
                </button>
            @else
                <span></span>
            @endunless

            <div class="flex gap-2">
                <a href="{{ route('purchases.index') }}" class="btn btn-ghost">Kembali</a>
                @unless ($posted)
                    <button type="submit" class="btn btn-outline">
                        <i class="ri-save-line"></i> Simpan Draft
                    </button>
                    <button type="button" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700"
                        data-confirm="Posting pembelian? Stok bahan akan ditambahkan dan dokumen tidak bisa diubah lagi."
                        wire:click="post">
                        <i class="ri-check-double-line"></i> Posting & Tambah Stok
                    </button>
                @endunless
            </div>
        </div>
    </form>
</div>
