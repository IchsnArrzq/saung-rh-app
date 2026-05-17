<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Nama Kategori</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="name" required>
                @error('name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Slug</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="slug"
                    placeholder="otomatis jika kosong">
                @error('slug')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Deskripsi</legend>
                <textarea class="textarea textarea-bordered w-full" rows="4" wire:model.defer="description"></textarea>
                @error('description')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Status</legend>
                <label class="label cursor-pointer justify-start gap-3 px-0">
                    <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_active">
                    <span class="label-text">Kategori aktif</span>
                </label>
                @error('is_active')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $menuCategory ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('menu-categories.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
