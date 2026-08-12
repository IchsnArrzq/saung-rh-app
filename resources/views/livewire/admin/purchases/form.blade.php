<div class="space-y-5">
    @include('admin.partials.flash')

    @php($posted = $purchase && $purchase->status === 'posted')

    <form wire:submit="save" class="space-y-5">
        <x-card>
            <div class="grid gap-4 md:grid-cols-3">
                <x-select label="Supplier" name="supplier_id" placeholder="Tanpa supplier"
                    wire:model.defer="supplier_id" :disabled="$posted">
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </x-select>

                <x-input label="Tanggal" name="purchase_date" type="date"
                    wire:model.defer="purchase_date" :disabled="$posted" />

                <x-input label="Catatan" name="notes" wire:model.defer="notes" :disabled="$posted" />
            </div>
        </x-card>

        <x-data-table :zebra="false">
            <x-slot:head>
                <tr>
                    <th class="w-2/5">Bahan</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Subtotal</th>
                    <th class="w-10"></th>
                </tr>
            </x-slot:head>

            @php($total = 0)
            @forelse ($items as $index => $row)
                @php($subtotal = (float) ($row['qty'] ?: 0) * (float) ($row['unit_cost'] ?: 0))
                @php($total += $subtotal)
                <tr wire:key="purchase-item-{{ $index }}">
                    <td>
                        <x-select :bare="true" size="sm" class="w-full" label="Bahan baris {{ $index + 1 }}"
                            name="items.{{ $index }}.ingredient_id" placeholder="-- Pilih Bahan --"
                            wire:model="items.{{ $index }}.ingredient_id" :disabled="$posted">
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit }})</option>
                            @endforeach
                        </x-select>
                        @error("items.{$index}.ingredient_id")
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </td>
                    <td>
                        <x-input :bare="true" type="number" size="sm" step="0.001" min="0.001"
                            class="w-28 text-right" label="Qty baris {{ $index + 1 }}"
                            wire:model.live.debounce.400ms="items.{{ $index }}.qty" :disabled="$posted" />
                        @error("items.{$index}.qty")
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </td>
                    <td>
                        <x-input :bare="true" type="number" size="sm" step="0.01" min="0"
                            class="w-32 text-right" label="Harga satuan baris {{ $index + 1 }}"
                            wire:model.live.debounce.400ms="items.{{ $index }}.unit_cost" :disabled="$posted" />
                        @error("items.{$index}.unit_cost")
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </td>
                    <td class="whitespace-nowrap text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    <td>
                        @unless ($posted)
                            <x-button variant="ghost" size="sm" shape="square" icon="ri-delete-bin-line"
                                class="text-error" label="Hapus baris {{ $index + 1 }}"
                                wire:click="removeRow({{ $index }})" />
                        @endunless
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-base-content/50">Belum ada item.</td>
                </tr>
            @endforelse

            <x-slot:foot>
                <tr>
                    <th colspan="3" class="text-right">Total</th>
                    <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
                    <th></th>
                </tr>
            </x-slot:foot>
        </x-data-table>

        @error('items')
            <x-alert type="error">{{ $message }}</x-alert>
        @enderror

        <div class="flex flex-wrap items-center justify-between gap-2">
            @unless ($posted)
                <x-button variant="outline" size="sm" icon="ri-add-line" wire:click="addRow">
                    Tambah Item
                </x-button>
            @else
                <span></span>
            @endunless

            <div class="flex gap-2">
                <x-button variant="ghost" :href="route('purchases.index')">Kembali</x-button>

                @unless ($posted)
                    <x-button type="submit" variant="outline" icon="ri-save-line" loading="save">
                        Simpan Draft
                    </x-button>
                    <x-button variant="primary" icon="ri-check-double-line" wire:click="post" loading="post"
                        data-confirm="Posting pembelian? Stok bahan akan ditambahkan dan dokumen tidak bisa diubah lagi."
                        data-confirm-title="Posting Pembelian" data-confirm-yes="Ya, Posting">
                        Posting & Tambah Stok
                    </x-button>
                @endunless
            </div>
        </div>
    </form>
</div>
