@php
    $reservation = $reservation ?? new \App\Models\Reservation();
    $reservationAt = old('reservation_at');
    if ($reservationAt === null && $reservation->reservation_at) {
        $reservationAt = $reservation->reservation_at->format('Y-m-d\TH:i');
    }
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="form-control">
        <span class="label-text">Nama Pelanggan</span>
        <input type="text" name="customer_name" class="input input-bordered" value="{{ old('customer_name', $reservation->customer_name) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">No Telepon</span>
        <input type="text" name="phone" class="input input-bordered" value="{{ old('phone', $reservation->phone) }}">
    </label>

    <label class="form-control">
        <span class="label-text">Meja</span>
        <select name="table_id" class="select select-bordered">
            <option value="">Belum ditentukan</option>
            @foreach ($tables as $table)
                <option value="{{ $table->id }}" @selected((string) old('table_id', $reservation->table_id) === (string) $table->id)>
                    {{ $table->code }} - {{ $table->name ?: 'Tanpa nama' }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Jumlah Tamu (Pax)</span>
        <input type="number" min="1" name="pax" class="input input-bordered" value="{{ old('pax', $reservation->pax ?? 1) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Waktu Reservasi</span>
        <input type="datetime-local" name="reservation_at" class="input input-bordered" value="{{ $reservationAt }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Status</span>
        <select name="status" class="select select-bordered" required>
            @foreach ($statusOptions as $status)
                <option value="{{ $status }}" @selected(old('status', $reservation->status ?? 'pending') === $status)>
                    {{ str_replace('_', ' ', $status) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control md:col-span-2">
        <span class="label-text">Catatan</span>
        <textarea name="notes" class="textarea textarea-bordered" rows="4">{{ old('notes', $reservation->notes) }}</textarea>
    </label>
</div>
