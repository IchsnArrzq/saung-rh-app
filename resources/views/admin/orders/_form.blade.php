@php
    $order = $order ?? null;
    $baseItems = old('items');

    if ($baseItems === null) {
        if (isset($order)) {
            $baseItems = $order->items->map(function ($item) {
                return [
                    'menu_id' => $item->menu_id,
                    'menu_name_snapshot' => $item->menu_name_snapshot,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'notes' => $item->notes,
                ];
            })->toArray();
        } else {
            $baseItems = [
                ['menu_id' => '', 'menu_name_snapshot' => '', 'qty' => 1, 'price' => 0, 'notes' => ''],
            ];
        }
    }

    $orderedAtValue = old('ordered_at');

    if ($orderedAtValue === null && isset($order) && $order->ordered_at) {
        $orderedAtValue = $order->ordered_at->format('Y-m-d\TH:i');
    }
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="form-control">
        <span class="label-text">Meja</span>
        <select name="table_id" class="select select-bordered">
            <option value="">Tanpa meja</option>
            @foreach ($tables as $table)
                <option value="{{ $table->id }}" @selected((string) old('table_id', $order?->table_id) === (string) $table->id)>
                    {{ $table->code }} - {{ $table->name ?: 'Tanpa nama' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Nama Pelanggan</span>
        <input type="text" name="customer_name" class="input input-bordered" value="{{ old('customer_name', $order?->customer_name) }}">
    </label>

    <label class="form-control">
        <span class="label-text">Status</span>
        <select name="status" class="select select-bordered" required>
            @foreach ($statusOptions as $status)
                <option value="{{ $status }}" @selected(old('status', $order?->status ?? 'draft') === $status)>
                    {{ str_replace('_', ' ', $status) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Waktu Order</span>
        <input type="datetime-local" name="ordered_at" class="input input-bordered" value="{{ $orderedAtValue }}">
    </label>

    <label class="form-control">
        <span class="label-text">Diskon</span>
        <input type="number" step="0.01" min="0" name="discount" class="input input-bordered" value="{{ old('discount', $order?->discount ?? 0) }}">
    </label>

    <label class="form-control">
        <span class="label-text">Pajak</span>
        <input type="number" step="0.01" min="0" name="tax" class="input input-bordered" value="{{ old('tax', $order?->tax ?? 0) }}">
    </label>

    <label class="form-control md:col-span-2">
        <span class="label-text">Catatan</span>
        <textarea name="notes" class="textarea textarea-bordered" rows="3">{{ old('notes', $order?->notes) }}</textarea>
    </label>
</div>

<div class="rounded-2xl border border-stone-200 p-4">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="font-semibold">Item Pesanan</h3>
        <button type="button" id="add-order-item" class="btn btn-xs btn-outline">Tambah Item</button>
    </div>

    <div id="order-items" class="space-y-3">
        @foreach ($baseItems as $index => $item)
            @php
                $selectedMenu = $menus->firstWhere('id', $item['menu_id'] ?? null);
                $itemName = $item['menu_name_snapshot'] ?? ($selectedMenu->name ?? '');
                $itemPrice = $item['price'] ?? ($selectedMenu->price ?? 0);
            @endphp
            <div class="order-item grid gap-3 rounded-xl border border-stone-200 p-3 md:grid-cols-5" data-index="{{ $index }}">
                <label class="form-control md:col-span-2">
                    <span class="label-text text-xs">Menu</span>
                    <select name="items[{{ $index }}][menu_id]" class="select select-bordered select-sm menu-select">
                        <option value="">Manual</option>
                        @foreach ($menus as $menu)
                            <option value="{{ $menu->id }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}" @selected((string) ($item['menu_id'] ?? '') === (string) $menu->id)>
                                {{ $menu->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="form-control md:col-span-2">
                    <span class="label-text text-xs">Nama Item</span>
                    <input type="text" name="items[{{ $index }}][menu_name_snapshot]" class="input input-bordered input-sm item-name"
                        value="{{ $itemName }}" required>
                </label>

                <label class="form-control">
                    <span class="label-text text-xs">Qty</span>
                    <input type="number" min="1" name="items[{{ $index }}][qty]" class="input input-bordered input-sm"
                        value="{{ $item['qty'] ?? 1 }}" required>
                </label>

                <label class="form-control md:col-span-2">
                    <span class="label-text text-xs">Harga</span>
                    <input type="number" min="0" step="0.01" name="items[{{ $index }}][price]" class="input input-bordered input-sm item-price"
                        value="{{ $itemPrice }}" required>
                </label>

                <label class="form-control md:col-span-2">
                    <span class="label-text text-xs">Catatan</span>
                    <input type="text" name="items[{{ $index }}][notes]" class="input input-bordered input-sm"
                        value="{{ $item['notes'] ?? '' }}" placeholder="opsional">
                </label>

                <div class="md:col-span-1 flex items-end justify-end">
                    <button type="button" class="btn btn-sm btn-error text-white remove-item">Hapus</button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<template id="order-item-template">
    <div class="order-item grid gap-3 rounded-xl border border-stone-200 p-3 md:grid-cols-5" data-index="__INDEX__">
        <label class="form-control md:col-span-2">
            <span class="label-text text-xs">Menu</span>
            <select name="items[__INDEX__][menu_id]" class="select select-bordered select-sm menu-select">
                <option value="">Manual</option>
                @foreach ($menus as $menu)
                    <option value="{{ $menu->id }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}">{{ $menu->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="form-control md:col-span-2">
            <span class="label-text text-xs">Nama Item</span>
            <input type="text" name="items[__INDEX__][menu_name_snapshot]" class="input input-bordered input-sm item-name" required>
        </label>

        <label class="form-control">
            <span class="label-text text-xs">Qty</span>
            <input type="number" min="1" name="items[__INDEX__][qty]" class="input input-bordered input-sm" value="1" required>
        </label>

        <label class="form-control md:col-span-2">
            <span class="label-text text-xs">Harga</span>
            <input type="number" min="0" step="0.01" name="items[__INDEX__][price]" class="input input-bordered input-sm item-price" value="0" required>
        </label>

        <label class="form-control md:col-span-2">
            <span class="label-text text-xs">Catatan</span>
            <input type="text" name="items[__INDEX__][notes]" class="input input-bordered input-sm" placeholder="opsional">
        </label>

        <div class="md:col-span-1 flex items-end justify-end">
            <button type="button" class="btn btn-sm btn-error text-white remove-item">Hapus</button>
        </div>
    </div>
</template>

<script>
    (() => {
        const list = document.getElementById('order-items');
        const template = document.getElementById('order-item-template');
        const addButton = document.getElementById('add-order-item');

        if (!list || !template || !addButton) {
            return;
        }

        const nextIndex = () => {
            const items = [...list.querySelectorAll('.order-item')];
            if (!items.length) {
                return 0;
            }

            const max = Math.max(...items.map((item) => Number(item.dataset.index || 0)));
            return max + 1;
        };

        addButton.addEventListener('click', () => {
            const index = nextIndex();
            const html = template.innerHTML.replaceAll('__INDEX__', index);
            list.insertAdjacentHTML('beforeend', html);
        });

        list.addEventListener('click', (event) => {
            if (!(event.target instanceof HTMLElement)) {
                return;
            }

            if (!event.target.classList.contains('remove-item')) {
                return;
            }

            const rows = list.querySelectorAll('.order-item');
            if (rows.length <= 1) {
                return;
            }

            event.target.closest('.order-item')?.remove();
        });

        list.addEventListener('change', (event) => {
            if (!(event.target instanceof HTMLSelectElement) || !event.target.classList.contains('menu-select')) {
                return;
            }

            const option = event.target.selectedOptions[0];
            const row = event.target.closest('.order-item');
            if (!row || !option) {
                return;
            }

            const itemNameInput = row.querySelector('.item-name');
            const itemPriceInput = row.querySelector('.item-price');

            if (itemNameInput instanceof HTMLInputElement && option.dataset.name) {
                itemNameInput.value = option.dataset.name;
            }

            if (itemPriceInput instanceof HTMLInputElement && option.dataset.price) {
                itemPriceInput.value = option.dataset.price;
            }
        });
    })();
</script>
