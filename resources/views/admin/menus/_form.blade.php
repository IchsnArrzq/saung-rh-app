@php
    $menu = $menu ?? new \App\Models\Menu();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="form-control md:col-span-2">
        <span class="label-text">Nama Menu</span>
        <input type="text" name="name" class="input input-bordered" value="{{ old('name', $menu->name) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Kategori</span>
        <select name="menu_category_id" class="select select-bordered">
            <option value="">Tanpa kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('menu_category_id', $menu->menu_category_id) === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Harga</span>
        <input type="number" step="0.01" min="0" name="price" class="input input-bordered" value="{{ old('price', $menu->price ?? 0) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Slug</span>
        <input type="text" name="slug" class="input input-bordered" value="{{ old('slug', $menu->slug) }}" placeholder="otomatis jika kosong">
    </label>

    <label class="form-control">
        <span class="label-text">SKU</span>
        <input type="text" name="sku" class="input input-bordered" value="{{ old('sku', $menu->sku) }}">
    </label>

    <label class="form-control md:col-span-2">
        <span class="label-text">URL Gambar</span>
        <input type="text" name="image_url" class="input input-bordered" value="{{ old('image_url', $menu->image_url) }}" placeholder="https://...">
    </label>

    <label class="form-control md:col-span-2">
        <span class="label-text">Deskripsi</span>
        <textarea name="description" class="textarea textarea-bordered" rows="4">{{ old('description', $menu->description) }}</textarea>
    </label>

    <label class="label cursor-pointer justify-start gap-3 md:col-span-2">
        <input type="hidden" name="is_available" value="0">
        <input type="checkbox" name="is_available" value="1" class="checkbox checkbox-sm" @checked((bool) old('is_available', $menu->is_available ?? true))>
        <span class="label-text">Menu tersedia</span>
    </label>
</div>
