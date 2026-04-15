@php
    $table = $table ?? new \App\Models\Table();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="form-control">
        <span class="label-text">Kode Meja</span>
        <input type="text" name="code" class="input input-bordered" value="{{ old('code', $table->code) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Nama Meja</span>
        <input type="text" name="name" class="input input-bordered" value="{{ old('name', $table->name) }}" placeholder="Opsional">
    </label>

    <label class="form-control">
        <span class="label-text">Kapasitas</span>
        <input type="number" name="capacity" min="1" class="input input-bordered" value="{{ old('capacity', $table->capacity ?? 4) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Status</span>
        <select name="status" class="select select-bordered" required>
            @foreach ($statusOptions as $status)
                <option value="{{ $status }}" @selected(old('status', $table->status ?? 'available') === $status)>
                    {{ str_replace('_', ' ', $status) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control md:col-span-2">
        <span class="label-text">Catatan</span>
        <textarea name="notes" class="textarea textarea-bordered" rows="4">{{ old('notes', $table->notes) }}</textarea>
    </label>
</div>
