<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Meja</legend>
                <select class="select select-bordered w-full" wire:model.defer="table_id">
                    <option value="">Tanpa meja</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}">{{ $table->code }} - {{ $table->name ?: 'Tanpa nama' }}</option>
                    @endforeach
                </select>
                @error('table_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nama Pelanggan</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="customer_name">
                @error('customer_name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Status</legend>
                <select class="select select-bordered w-full" wire:model.defer="status" required>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}">{{ str_replace('_', ' ', $statusOption) }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Waktu Order</legend>
                <input type="datetime-local" class="input input-bordered w-full" wire:model.defer="ordered_at">
                @error('ordered_at')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Pajak</legend>
                <input type="number" step="0.01" min="0" class="input input-bordered w-full" wire:model.defer="tax">
                @error('tax')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Catatan</legend>
                <textarea class="textarea textarea-bordered w-full" rows="3" wire:model.defer="notes"></textarea>
                @error('notes')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="rounded-2xl border border-stone-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-semibold">Item Pesanan</h3>
                <button type="button" class="btn btn-xs btn-outline" wire:click="addItem">Tambah Item</button>
            </div>

            @error('items')
                <p class="label text-error mb-2">{{ $message }}</p>
            @enderror

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div class="grid gap-3 rounded-xl border border-stone-200 p-3 md:grid-cols-5"
                        wire:key="order-item-{{ $index }}">
                        <fieldset class="fieldset md:col-span-2">
                            <legend class="fieldset-legend text-xs">Menu</legend>
                            <select class="select select-bordered select-sm w-full" wire:model="items.{{ $index }}.menu_id"
                                wire:change="applyMenu({{ $index }})">
                                <option value="">Manual</option>
                                @foreach ($menus as $menu)
                                    <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                                @endforeach
                            </select>
                            @error("items.$index.menu_id")
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <fieldset class="fieldset md:col-span-2">
                            <legend class="fieldset-legend text-xs">Nama Item</legend>
                            <input type="text" class="input input-bordered input-sm w-full"
                                wire:model.defer="items.{{ $index }}.menu_name_snapshot" required>
                            @error("items.$index.menu_name_snapshot")
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">Qty</legend>
                            <input type="number" min="1" class="input input-bordered input-sm w-full"
                                wire:model.defer="items.{{ $index }}.qty" required>
                            @error("items.$index.qty")
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <fieldset class="fieldset md:col-span-2">
                            <legend class="fieldset-legend text-xs">Harga</legend>
                            <input type="number" min="0" step="0.01" class="input input-bordered input-sm w-full"
                                wire:model.defer="items.{{ $index }}.price" required>
                            @error("items.$index.price")
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <fieldset class="fieldset md:col-span-2">
                            <legend class="fieldset-legend text-xs">Catatan</legend>
                            <input type="text" class="input input-bordered input-sm w-full"
                                wire:model.defer="items.{{ $index }}.notes" placeholder="Catatan item">
                            @error("items.$index.notes")
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div class="md:col-span-1 flex items-end justify-end">
                            <button type="button" class="btn btn-sm btn-error text-white"
                                wire:click="removeItem({{ $index }})">
                                Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $order ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('orders.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
