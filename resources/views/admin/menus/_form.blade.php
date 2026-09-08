@php
    $menu = $menu ?? new \App\Models\Menu();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <x-input label="Nama menu" name="name" value="{{ old('name', $menu->name) }}" required autofocus
        fieldClass="md:col-span-2" />

    <x-select label="Kategori" name="menu_category_id" placeholder="Tanpa kategori"
        :selected="old('menu_category_id', $menu->menu_category_id)"
        :options="$categories->pluck('name', 'id')->all()" />

    <x-input label="Harga" name="price" type="number" step="0.01" min="0" inputmode="numeric"
        value="{{ old('price', $menu->price ?? 0) }}" required />

    <x-input label="Slug" name="slug" value="{{ old('slug', $menu->slug) }}" placeholder="otomatis jika kosong"
        hint="Dipakai di URL. Kosongkan untuk dibuatkan otomatis." />

    <x-input label="SKU" name="sku" value="{{ old('sku', $menu->sku) }}" />

    <x-input label="URL gambar" name="image_url" type="url" value="{{ old('image_url', $menu->image_url) }}"
        placeholder="https://..." fieldClass="md:col-span-2" />

    <x-textarea label="Deskripsi" name="description" rows="4" fieldClass="md:col-span-2">{{ old('description', $menu->description) }}</x-textarea>

    <div class="md:col-span-2">
        <input type="hidden" name="is_available" value="0">
        <x-checkbox size="sm" name="is_available" value="1" label="Menu tersedia"
            :checked="(bool) old('is_available', $menu->is_available ?? true)" />
        @error('is_available')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>
</div>
