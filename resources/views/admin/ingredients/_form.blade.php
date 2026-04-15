@php
    $ingredient = $ingredient ?? new \App\Models\Ingredient();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="form-control">
        <span class="label-text">Nama Bahan</span>
        <input type="text" name="name" class="input input-bordered" value="{{ old('name', $ingredient->name) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Satuan</span>
        <input type="text" name="unit" class="input input-bordered" value="{{ old('unit', $ingredient->unit) }}" placeholder="gram, ml, pcs" required>
    </label>

    <label class="form-control">
        <span class="label-text">Stok Saat Ini</span>
        <input type="number" step="0.01" min="0" name="current_stock" class="input input-bordered" value="{{ old('current_stock', $ingredient->current_stock ?? 0) }}">
    </label>

    <label class="form-control">
        <span class="label-text">Minimal Stok</span>
        <input type="number" step="0.01" min="0" name="minimum_stock" class="input input-bordered" value="{{ old('minimum_stock', $ingredient->minimum_stock ?? 0) }}">
    </label>

    <label class="label cursor-pointer justify-start gap-3 md:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" @checked((bool) old('is_active', $ingredient->is_active ?? true))>
        <span class="label-text">Bahan aktif</span>
    </label>

    <label class="form-control md:col-span-2">
        <span class="label-text">Catatan</span>
        <textarea name="notes" class="textarea textarea-bordered" rows="4">{{ old('notes', $ingredient->notes) }}</textarea>
    </label>
</div>
