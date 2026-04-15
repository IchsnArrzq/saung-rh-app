@php
    $table = $table ?? new \App\Models\Table();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Kode Meja</legend>
        <input type="text" name="code" class="input input-bordered w-full" value="{{ old('code', $table->code) }}" required>
        @error('code')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Nama Meja</legend>
        <input type="text" name="name" class="input input-bordered w-full" value="{{ old('name', $table->name) }}">
        @error('name')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Kapasitas</legend>
        <input type="number" name="capacity" min="1" class="input input-bordered w-full" value="{{ old('capacity', $table->capacity ?? 4) }}" required>
        @error('capacity')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Status</legend>
        <select name="table_status_id" class="select select-bordered w-full" required>
            @if ($statusOptions->isEmpty())
                <option value="">Belum ada status meja aktif</option>
            @else
                @foreach ($statusOptions as $status)
                    <option value="{{ $status->id }}" @selected(old('table_status_id', $table->table_status_id) === $status->id)>
                        {{ $status->name }}
                    </option>
                @endforeach
            @endif
        </select>
        @error('table_status_id')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Kategori Meja</legend>
        <select name="table_category_id" class="select select-bordered w-full">
            <option value="">Tanpa kategori</option>
            @foreach ($categoryOptions as $category)
                <option value="{{ $category->id }}" @selected(old('table_category_id', $table->table_category_id) === $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('table_category_id')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Catatan</legend>
        <textarea name="notes" class="textarea textarea-bordered w-full" rows="4">{{ old('notes', $table->notes) }}</textarea>
        @error('notes')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>
</div>
