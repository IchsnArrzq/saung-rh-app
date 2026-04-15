@php
    $promotion = $promotion ?? new \App\Models\Promotion();
    $startsAt = old('starts_at');
    if ($startsAt === null && $promotion->starts_at) {
        $startsAt = $promotion->starts_at->format('Y-m-d\TH:i');
    }

    $endsAt = old('ends_at');
    if ($endsAt === null && $promotion->ends_at) {
        $endsAt = $promotion->ends_at->format('Y-m-d\TH:i');
    }
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="form-control">
        <span class="label-text">Kode Promo</span>
        <input type="text" name="code" class="input input-bordered" value="{{ old('code', $promotion->code) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Nama Promo</span>
        <input type="text" name="name" class="input input-bordered" value="{{ old('name', $promotion->name) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Tipe</span>
        <select name="type" class="select select-bordered" required>
            @foreach ($typeOptions as $type)
                <option value="{{ $type }}" @selected(old('type', $promotion->type ?? 'percent') === $type)>
                    {{ strtoupper($type) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Nilai Diskon</span>
        <input type="number" step="0.01" min="0" name="value" class="input input-bordered" value="{{ old('value', $promotion->value) }}">
    </label>

    <label class="form-control">
        <span class="label-text">Minimal Pembelian</span>
        <input type="number" step="0.01" min="0" name="min_purchase" class="input input-bordered" value="{{ old('min_purchase', $promotion->min_purchase ?? 0) }}">
    </label>

    <label class="form-control">
        <span class="label-text">Mulai Berlaku</span>
        <input type="datetime-local" name="starts_at" class="input input-bordered" value="{{ $startsAt }}">
    </label>

    <label class="form-control">
        <span class="label-text">Berakhir</span>
        <input type="datetime-local" name="ends_at" class="input input-bordered" value="{{ $endsAt }}">
    </label>

    <label class="label cursor-pointer justify-start gap-3 md:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" @checked((bool) old('is_active', $promotion->is_active ?? true))>
        <span class="label-text">Promo aktif</span>
    </label>

    <label class="form-control md:col-span-2">
        <span class="label-text">Deskripsi</span>
        <textarea name="description" class="textarea textarea-bordered" rows="4">{{ old('description', $promotion->description) }}</textarea>
    </label>
</div>
