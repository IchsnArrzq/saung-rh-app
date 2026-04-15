@php
    $menuCategory = $menuCategory ?? new \App\Models\MenuCategory();
@endphp

<div class="grid gap-4">
    <label class="form-control">
        <span class="label-text">Nama Kategori</span>
        <input type="text" name="name" class="input input-bordered" value="{{ old('name', $menuCategory->name) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Slug</span>
        <input type="text" name="slug" class="input input-bordered" value="{{ old('slug', $menuCategory->slug) }}" placeholder="otomatis jika kosong">
    </label>

    <label class="form-control">
        <span class="label-text">Deskripsi</span>
        <textarea name="description" class="textarea textarea-bordered" rows="4">{{ old('description', $menuCategory->description) }}</textarea>
    </label>

    <label class="label cursor-pointer justify-start gap-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" @checked((bool) old('is_active', $menuCategory->is_active ?? true))>
        <span class="label-text">Kategori aktif</span>
    </label>
</div>
