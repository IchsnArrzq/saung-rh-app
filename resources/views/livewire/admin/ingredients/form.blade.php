<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Nama Bahan</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="name" required>
                @error('name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Satuan</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="unit"
                    placeholder="gram, ml, butir, buah, liter...">
                @error('unit')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Harga per Satuan (Rp)</legend>
                <input type="number" step="0.01" min="0" class="input input-bordered w-full"
                    wire:model.defer="cost_per_unit" placeholder="Opsional">
                @error('cost_per_unit')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Stok Awal</legend>
                <input type="number" step="0.001" min="0" class="input input-bordered w-full" wire:model.defer="stock">
                @error('stock')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Stok Minimum (alert)</legend>
                <input type="number" step="0.001" min="0" class="input input-bordered w-full" wire:model.defer="min_stock">
                @error('min_stock')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Status</legend>
                <label class="label cursor-pointer justify-start gap-3 px-0">
                    <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_active">
                    <span class="label-text">Bahan aktif digunakan</span>
                </label>
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $ingredient ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('ingredients.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
