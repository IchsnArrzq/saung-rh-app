@php
    $reservation = $reservation ?? new \App\Models\Reservation();
    $reservationAt = old('reservation_at');
    if ($reservationAt === null && $reservation->reservation_at) {
        $reservationAt = $reservation->reservation_at->format('Y-m-d\\TH:i');
    }
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Nama Pelanggan</legend>
        <input type="text" name="customer_name" class="input input-bordered w-full" value="{{ old('customer_name', $reservation->customer_name) }}" required>
        @error('customer_name')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">No Telepon</legend>
        <input type="text" name="phone" class="input input-bordered w-full" value="{{ old('phone', $reservation->phone) }}">
        @error('phone')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Meja</legend>
        <select name="table_id" class="select select-bordered w-full">
            <option value="">Belum ditentukan</option>
            @foreach ($tables as $table)
                <option value="{{ $table->id }}" @selected((string) old('table_id', $reservation->table_id) === (string) $table->id)>
                    {{ $table->code }} - {{ $table->name ?: 'Tanpa nama' }}
                </option>
            @endforeach
        </select>
        @error('table_id')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Jumlah Tamu (Pax)</legend>
        <input type="number" min="1" name="pax" class="input input-bordered w-full" value="{{ old('pax', $reservation->pax ?? 1) }}" required>
        @error('pax')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Waktu Reservasi</legend>
        <input type="datetime-local" name="reservation_at" class="input input-bordered w-full" value="{{ $reservationAt }}" required>
        @error('reservation_at')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Status</legend>
        <select name="status" class="select select-bordered w-full" required>
            @foreach ($statusOptions as $status)
                <option value="{{ $status }}" @selected(old('status', $reservation->status ?? 'pending') === $status)>
                    {{ str_replace('_', ' ', $status) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Catatan</legend>
        <textarea name="notes" class="textarea textarea-bordered w-full" rows="4">{{ old('notes', $reservation->notes) }}</textarea>
        @error('notes')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>
</div>
