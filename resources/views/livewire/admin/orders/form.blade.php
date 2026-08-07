<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <x-card>
            <div class="grid gap-4 md:grid-cols-2">
                <x-select label="Meja" name="table_id" placeholder="Tanpa meja" wire:model.defer="table_id">
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}">{{ $table->code }} - {{ $table->name ?: 'Tanpa nama' }}</option>
                    @endforeach
                </x-select>

                <x-input label="Nama Pelanggan" name="customer_name" wire:model.defer="customer_name" />

                <x-select label="Status" name="status" :required="true" wire:model.defer="status" required>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}">
                            {{ \App\Domains\Order\Enums\OrderStatus::tryFrom((string) $statusOption)?->label()
                                ?? str_replace('_', ' ', $statusOption) }}
                        </option>
                    @endforeach
                </x-select>

                <x-input label="Waktu Order" name="ordered_at" type="datetime-local" wire:model.defer="ordered_at" />

                <x-input label="Pajak" name="tax" type="number" step="0.01" min="0" wire:model.defer="tax" />

                <x-textarea field-class="md:col-span-2" label="Catatan" name="notes" :rows="3"
                    wire:model.defer="notes" />
            </div>
        </x-card>

        <x-card title="Item Pesanan">
            <x-slot:actions>
                <x-button variant="outline" size="sm" icon="ri-add-line" wire:click="addItem">Tambah Item</x-button>
            </x-slot:actions>

            @error('items')
                <x-alert type="error" class="mb-3">{{ $message }}</x-alert>
            @enderror

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div class="grid gap-3 rounded-xl border border-base-300 p-3 md:grid-cols-5"
                        wire:key="order-item-{{ $index }}">
                        <x-select field-class="md:col-span-2" label="Menu" name="items.{{ $index }}.menu_id"
                            size="sm" placeholder="Manual"
                            wire:model="items.{{ $index }}.menu_id" wire:change="applyMenu({{ $index }})">
                            @foreach ($menus as $menu)
                                <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                            @endforeach
                        </x-select>

                        <x-input field-class="md:col-span-2" label="Nama Item"
                            name="items.{{ $index }}.menu_name_snapshot" size="sm" :required="true"
                            wire:model.defer="items.{{ $index }}.menu_name_snapshot" required />

                        <x-input label="Qty" name="items.{{ $index }}.qty" type="number" size="sm" min="1"
                            :required="true" wire:model.defer="items.{{ $index }}.qty" required />

                        <x-input field-class="md:col-span-2" label="Harga" name="items.{{ $index }}.price"
                            type="number" size="sm" min="0" step="0.01" :required="true"
                            wire:model.defer="items.{{ $index }}.price" required />

                        <x-input field-class="md:col-span-2" label="Catatan" name="items.{{ $index }}.notes"
                            size="sm" wire:model.defer="items.{{ $index }}.notes" placeholder="Catatan item" />

                        <div class="flex items-end justify-end md:col-span-1">
                            <x-button variant="error" size="sm" class="text-white"
                                wire:click="removeItem({{ $index }})">
                                Hapus
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-form-actions :submit-label="$order ? 'Update' : 'Simpan'" :cancel-href="route('orders.index')"
            loading="save" />
    </form>
</div>
