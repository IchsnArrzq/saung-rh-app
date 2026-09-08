@php
    $menuCategory = $menuCategory ?? new \App\Models\MenuCategory();
@endphp

<div class="grid gap-4">
    <x-input label="Nama kategori" name="name" value="{{ old('name', $menuCategory->name) }}" required autofocus />

    <x-input label="Slug" name="slug" value="{{ old('slug', $menuCategory->slug) }}"
        placeholder="otomatis jika kosong" hint="Dipakai di URL. Kosongkan untuk dibuatkan otomatis." />

    <x-textarea label="Deskripsi" name="description" rows="4">{{ old('description', $menuCategory->description) }}</x-textarea>

    <div>
        <input type="hidden" name="is_active" value="0">
        <x-checkbox size="sm" name="is_active" value="1" label="Kategori aktif"
            :checked="(bool) old('is_active', $menuCategory->is_active ?? true)" />
        @error('is_active')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>
</div>
