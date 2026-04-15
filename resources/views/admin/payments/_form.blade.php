@php
    $payment = $payment ?? new \App\Models\Payment();
    $paidAtValue = old('paid_at');

    if ($paidAtValue === null && $payment->paid_at) {
        $paidAtValue = $payment->paid_at->format('Y-m-d\\TH:i');
    }
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Order</legend>
        <select name="order_id" class="select select-bordered w-full" required>
            <option value="">Pilih order</option>
            @foreach ($orders as $order)
                <option value="{{ $order->id }}" @selected((string) old('order_id', $payment->order_id) === (string) $order->id)>
                    {{ $order->order_number }} - Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                </option>
            @endforeach
        </select>
        @error('order_id')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Metode</legend>
        <select name="method" class="select select-bordered w-full" required>
            @foreach ($methodOptions as $method)
                <option value="{{ $method }}" @selected(old('method', $payment->method ?? 'cash') === $method)>
                    {{ str_replace('_', ' ', $method) }}
                </option>
            @endforeach
        </select>
        @error('method')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <input type="hidden" name="type" value="full">

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Tipe Pembayaran</legend>
        <input type="text" class="input input-bordered w-full" value="FULL" readonly>
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Status</legend>
        <select name="status" class="select select-bordered w-full" required>
            @foreach ($statusOptions as $status)
                <option value="{{ $status }}" @selected(old('status', $payment->status ?? 'paid') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Jumlah</legend>
        <input type="number" step="0.01" min="0" name="amount" class="input input-bordered w-full" value="{{ old('amount', $payment->amount ?? 0) }}" required>
        @error('amount')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Referensi</legend>
        <input type="text" name="reference" class="input input-bordered w-full" value="{{ old('reference', $payment->reference) }}" placeholder="No struk / kode transaksi">
        @error('reference')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Waktu Bayar</legend>
        <input type="datetime-local" name="paid_at" class="input input-bordered w-full" value="{{ $paidAtValue }}">
        @error('paid_at')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Catatan</legend>
        <textarea name="notes" class="textarea textarea-bordered w-full" rows="4">{{ old('notes', $payment->notes) }}</textarea>
        @error('notes')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>
</div>
