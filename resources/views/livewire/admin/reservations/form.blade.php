<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <x-input label="Nama Pelanggan" name="customer_name" :required="true"
                wire:model.defer="customer_name" required />

            <x-input label="No Telepon" name="phone" wire:model.defer="phone" />

            <x-select label="Meja" name="table_id" placeholder="Belum ditentukan" wire:model.defer="table_id">
                @foreach ($tables as $table)
                    <option value="{{ $table->id }}">{{ $table->code }} - {{ $table->name ?: 'Tanpa nama' }}</option>
                @endforeach
            </x-select>

            <x-input label="Jumlah Tamu (Pax)" name="pax" type="number" min="1" :required="true"
                wire:model.defer="pax" required />

            <x-input label="Waktu Reservasi" name="reservation_at" type="datetime-local" :required="true"
                wire:model.defer="reservation_at" required />

            <x-select label="Status" name="status" :required="true" wire:model.defer="status" required>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                @endforeach
            </x-select>

            <x-textarea field-class="md:col-span-2" label="Catatan" name="notes" :rows="4"
                wire:model.defer="notes" />
        </div>

        <x-card title="Menu Reservasi"
            description="Item ini akan ikut muncul di detail reservasi dan bisa digenerate menjadi order.">
            <x-slot:actions>
                <x-button variant="outline" size="sm" icon="ri-add-line" wire:click="addItem">
                    Tambah Menu
                </x-button>
            </x-slot:actions>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div class="rounded-xl border border-base-300 p-3" wire:key="reservation-item-{{ $index }}">
                        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_140px_auto] md:items-end">
                            <x-select label="Menu" name="items.{{ $index }}.menu_id" size="sm" :required="true"
                                placeholder="Pilih menu" wire:model.defer="items.{{ $index }}.menu_id" required>
                                @foreach ($menus as $menu)
                                    <option value="{{ $menu->id }}">
                                        {{ $menu->name }} - Rp {{ number_format((float) $menu->price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </x-select>

                            <x-input label="Jumlah" name="items.{{ $index }}.qty" type="number" size="sm"
                                min="1" max="50" :required="true"
                                wire:model.defer="items.{{ $index }}.qty" required />

                            <x-button variant="error" size="sm" class="text-white"
                                wire:click="removeItem({{ $index }})" @disabled(count($items) <= 1)>
                                Hapus
                            </x-button>
                        </div>

                        <div class="mt-3">
                            <x-input label="Catatan Menu" name="items.{{ $index }}.notes" size="sm"
                                wire:model.defer="items.{{ $index }}.notes"
                                placeholder="contoh: kurang gula / ekstra pedas" />
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-form-actions :submit-label="$reservation ? 'Update' : 'Simpan'"
            :cancel-href="route('reservations.index')" loading="save" />
    </form>
</div>
