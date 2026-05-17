<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Nama Menu</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="name" required>
                @error('name')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Kategori</legend>
                <select class="select select-bordered w-full" wire:model.defer="menu_category_id">
                    <option value="">Tanpa kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('menu_category_id')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Harga</legend>
                <input type="number" step="0.01" min="0" class="input input-bordered w-full" wire:model.defer="price"
                    required>
                @error('price')
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
                <legend class="fieldset-legend">SKU</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="sku">
                @error('sku')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">URL Gambar</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="image_url"
                    placeholder="https://...">
                @error('image_url')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Deskripsi</legend>
                <textarea class="textarea textarea-bordered w-full" rows="4" wire:model.defer="description"></textarea>
                @error('description')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Ketersediaan</legend>
                <label class="label cursor-pointer justify-start gap-3 px-0">
                    <input type="checkbox" class="checkbox checkbox-sm" wire:model="is_available">
                    <span class="label-text">Menu tersedia</span>
                </label>
                @error('is_available')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $menu ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('menus.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
