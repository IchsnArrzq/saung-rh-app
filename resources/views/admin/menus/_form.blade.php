@php
    $menu = $menu ?? new \App\Models\Menu();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Nama Menu</legend>
        <input type="text" name="name" class="input input-bordered w-full" value="{{ old('name', $menu->name) }}" required>
        @error('name')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Kategori</legend>
        <select name="menu_category_id" class="select select-bordered w-full">
            <option value="">Tanpa kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('menu_category_id', $menu->menu_category_id) === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('menu_category_id')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Harga</legend>
        <input type="number" step="0.01" min="0" name="price" class="input input-bordered w-full" value="{{ old('price', $menu->price ?? 0) }}" required>
        @error('price')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Slug</legend>
        <input type="text" name="slug" class="input input-bordered w-full" value="{{ old('slug', $menu->slug) }}" placeholder="otomatis jika kosong">
        @error('slug')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">SKU</legend>
        <input type="text" name="sku" class="input input-bordered w-full" value="{{ old('sku', $menu->sku) }}">
        @error('sku')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">URL Gambar</legend>
        <input type="text" name="image_url" class="input input-bordered w-full" value="{{ old('image_url', $menu->image_url) }}" placeholder="https://...">
        @error('image_url')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Deskripsi</legend>
        <textarea name="description" class="textarea textarea-bordered w-full" rows="4">{{ old('description', $menu->description) }}</textarea>
        @error('description')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Ketersediaan</legend>
        <label class="label cursor-pointer justify-start gap-3 px-0">
            <input type="hidden" name="is_available" value="0">
            <input type="checkbox" name="is_available" value="1" class="checkbox checkbox-sm" @checked((bool) old('is_available', $menu->is_available ?? true))>
            <span class="label-text">Menu tersedia</span>
        </label>
        @error('is_available')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>
</div>
