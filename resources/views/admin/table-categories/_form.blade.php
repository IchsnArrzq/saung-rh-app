@php
    $tableCategory = $tableCategory ?? new \App\Models\TableCategory();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <x-input label="Nama kategori" name="name" value="{{ old('name', $tableCategory->name) }}" required autofocus />

    <x-input label="Slug" name="slug" value="{{ old('slug', $tableCategory->slug) }}"
        placeholder="otomatis jika kosong" hint="Dipakai di URL. Kosongkan untuk dibuatkan otomatis." />

    <x-input label="Urutan" name="sort_order" type="number" min="0" inputmode="numeric"
        value="{{ old('sort_order', $tableCategory->sort_order ?? 0) }}"
        hint="Angka kecil tampil lebih dulu." />

    <div>
        <input type="hidden" name="is_active" value="0">
        <x-checkbox size="sm" name="is_active" value="1" label="Kategori aktif"
            :checked="(bool) old('is_active', $tableCategory->is_active ?? true)" />
        @error('is_active')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>

    <x-textarea label="Deskripsi" name="description" rows="4" fieldClass="md:col-span-2">{{ old('description', $tableCategory->description) }}</x-textarea>
</div>
