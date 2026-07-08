<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Nama Pelanggan</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="name" required>
                @error('name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Kode</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="code"
                    placeholder="Opsional, mis. CUS-001">
                @error('code')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Telepon</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="phone">
                @error('phone')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Email</legend>
                <input type="email" class="input input-bordered w-full" wire:model.defer="email">
                @error('email')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Alamat</legend>
                <textarea class="textarea textarea-bordered w-full" rows="2" wire:model.defer="address"></textarea>
                @error('address')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Catatan</legend>
                <textarea class="textarea textarea-bordered w-full" rows="2" wire:model.defer="notes"></textarea>
                @error('notes')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Status</legend>
                <label class="label cursor-pointer justify-start gap-3 px-0">
                    <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_active">
                    <span class="label-text">Pelanggan aktif</span>
                </label>
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $customer ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('customers.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
