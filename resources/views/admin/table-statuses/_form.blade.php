@php
    $tableStatus = $tableStatus ?? new \App\Models\TableStatus();
    $reservedKeys = ['available', 'occupied', 'order_in', 'cleaning'];
    $isReservedStatus = $tableStatus->exists && in_array($tableStatus->key, $reservedKeys, true);
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Nama Status</legend>
        <input type="text" name="name" class="input input-bordered w-full" value="{{ old('name', $tableStatus->name) }}" required>
        @error('name')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Key Status</legend>
        <input type="text" name="key" class="input input-bordered w-full" value="{{ old('key', $tableStatus->key) }}" placeholder="available"
            @readonly($isReservedStatus) required>
        @if ($isReservedStatus)
            <p class="label">Key status sistem tidak bisa diubah.</p>
        @endif
        @error('key')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Warna</legend>
        <select name="color" class="select select-bordered w-full">
            @php
                $selectedColor = old('color', $tableStatus->color ?: 'neutral');
            @endphp
            <option value="success" @selected($selectedColor === 'success')>Success</option>
            <option value="error" @selected($selectedColor === 'error')>Error</option>
            <option value="warning" @selected($selectedColor === 'warning')>Warning</option>
            <option value="info" @selected($selectedColor === 'info')>Info</option>
            <option value="neutral" @selected($selectedColor === 'neutral')>Neutral</option>
        </select>
        @error('color')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Urutan</legend>
        <input type="number" name="sort_order" min="0" class="input input-bordered w-full" value="{{ old('sort_order', $tableStatus->sort_order ?? 0) }}">
        @error('sort_order')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Aktif</legend>
        <label class="label cursor-pointer justify-start gap-3 px-0">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm"
                @checked((bool) old('is_active', $tableStatus->is_active ?? true))>
            <span class="label-text">Status aktif</span>
        </label>
        @error('is_active')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Default</legend>
        <label class="label cursor-pointer justify-start gap-3 px-0">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" name="is_default" value="1" class="checkbox checkbox-sm"
                @checked((bool) old('is_default', $tableStatus->is_default ?? false))>
            <span class="label-text">Jadikan default</span>
        </label>
        @error('is_default')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>
</div>
