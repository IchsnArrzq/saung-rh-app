@php
    $tableCategory = $tableCategory ?? new \App\Models\TableCategory();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Nama Kategori</legend>
        <input type="text" name="name" class="input input-bordered w-full" value="{{ old('name', $tableCategory->name) }}" required>
        @error('name')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Slug</legend>
        <input type="text" name="slug" class="input input-bordered w-full" value="{{ old('slug', $tableCategory->slug) }}"
            placeholder="otomatis jika kosong">
        @error('slug')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Urutan</legend>
        <input type="number" name="sort_order" min="0" class="input input-bordered w-full"
            value="{{ old('sort_order', $tableCategory->sort_order ?? 0) }}">
        @error('sort_order')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Status</legend>
        <label class="label cursor-pointer justify-start gap-3 px-0">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm"
                @checked((bool) old('is_active', $tableCategory->is_active ?? true))>
            <span class="label-text">Kategori aktif</span>
        </label>
        @error('is_active')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Deskripsi</legend>
        <textarea name="description" class="textarea textarea-bordered w-full" rows="4">{{ old('description', $tableCategory->description) }}</textarea>
        @error('description')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>
</div>
