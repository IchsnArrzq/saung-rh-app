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

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $reservation ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('reservations.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
