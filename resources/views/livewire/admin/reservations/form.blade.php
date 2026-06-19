<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nama Pelanggan</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="customer_name" required>
                @error('customer_name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">No Telepon</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="phone">
                @error('phone')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Meja</legend>
                <select class="select select-bordered w-full" wire:model.defer="table_id">
                    <option value="">Belum ditentukan</option>
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}">{{ $table->code }} - {{ $table->name ?: 'Tanpa nama' }}</option>
                    @endforeach
                </select>
                @error('table_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Jumlah Tamu (Pax)</legend>
                <input type="number" min="1" class="input input-bordered w-full" wire:model.defer="pax" required>
                @error('pax')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Waktu Reservasi</legend>
                <input type="datetime-local" class="input input-bordered w-full" wire:model.defer="reservation_at"
                    required>
                @error('reservation_at')
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

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Catatan</legend>
                <textarea class="textarea textarea-bordered w-full" rows="4" wire:model.defer="notes"></textarea>
                @error('notes')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <section class="rounded-2xl border border-stone-200 bg-white p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-stone-900">Menu Reservasi</h3>
                    <p class="mt-1 text-sm text-stone-500">Item ini akan ikut muncul di detail reservasi dan bisa digenerate menjadi order.</p>
                </div>

                <button type="button" wire:click="addItem" class="btn btn-sm btn-outline">
                    <i class="ri-add-line"></i>
                    Tambah Menu
                </button>
            </div>

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div class="rounded-xl border border-stone-200 p-3" wire:key="reservation-item-{{ $index }}">
                        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_140px_auto] md:items-end">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Menu</legend>
                                <select class="select select-bordered select-sm w-full" wire:model.defer="items.{{ $index }}.menu_id" required>
                                    <option value="">Pilih menu</option>
                                    @foreach ($menus as $menu)
                                        <option value="{{ $menu->id }}">
                                            {{ $menu->name }} - Rp {{ number_format((float) $menu->price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('items.'.$index.'.menu_id')
                                    <p class="label text-error">{{ $message }}</p>
                                @enderror
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Jumlah</legend>
                                <input type="number" min="1" max="50" class="input input-bordered input-sm w-full"
                                    wire:model.defer="items.{{ $index }}.qty" required>
                                @error('items.'.$index.'.qty')
                                    <p class="label text-error">{{ $message }}</p>
                                @enderror
                            </fieldset>

                            <button type="button" wire:click="removeItem({{ $index }})"
                                class="btn btn-sm btn-error text-white" @disabled(count($items) <= 1)>
                                Hapus
                            </button>
                        </div>

                        <fieldset class="fieldset mt-3">
                            <legend class="fieldset-legend text-xs">Catatan Menu</legend>
                            <input type="text" class="input input-bordered input-sm w-full"
                                wire:model.defer="items.{{ $index }}.notes"
                                placeholder="contoh: kurang gula / ekstra pedas">
                            @error('items.'.$index.'.notes')
                                <p class="label text-error">{{ $message }}</p>
                            @enderror
                        </fieldset>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $reservation ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('reservations.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
