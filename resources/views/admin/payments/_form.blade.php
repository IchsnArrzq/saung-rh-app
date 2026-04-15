@php
    $payment = $payment ?? new \App\Models\Payment();
    $paidAtValue = old('paid_at');

    if ($paidAtValue === null && $payment->paid_at) {
        $paidAtValue = $payment->paid_at->format('Y-m-d\TH:i');
    }
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="form-control md:col-span-2">
        <span class="label-text">Order</span>
        <select name="order_id" class="select select-bordered" required>
            <option value="">Pilih order</option>
            @foreach ($orders as $order)
                <option value="{{ $order->id }}" @selected((string) old('order_id', $payment->order_id) === (string) $order->id)>
                    {{ $order->order_number }} - Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Metode</span>
        <select name="method" class="select select-bordered" required>
            @foreach ($methodOptions as $method)
                <option value="{{ $method }}" @selected(old('method', $payment->method ?? 'cash') === $method)>
                    {{ str_replace('_', ' ', $method) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Tipe Pembayaran</span>
        <select name="type" class="select select-bordered" required>
            @foreach ($typeOptions as $type)
                <option value="{{ $type }}" @selected(old('type', $payment->type ?? 'full') === $type)>
                    {{ strtoupper($type) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Status</span>
        <select name="status" class="select select-bordered" required>
            @foreach ($statusOptions as $status)
                <option value="{{ $status }}" @selected(old('status', $payment->status ?? 'paid') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="form-control">
        <span class="label-text">Jumlah</span>
        <input type="number" step="0.01" min="0" name="amount" class="input input-bordered" value="{{ old('amount', $payment->amount ?? 0) }}" required>
    </label>

    <label class="form-control">
        <span class="label-text">Referensi</span>
        <input type="text" name="reference" class="input input-bordered" value="{{ old('reference', $payment->reference) }}" placeholder="No struk / kode transaksi">
    </label>

    <label class="form-control">
        <span class="label-text">Waktu Bayar</span>
        <input type="datetime-local" name="paid_at" class="input input-bordered" value="{{ $paidAtValue }}">
    </label>

    <label class="form-control md:col-span-2">
        <span class="label-text">Catatan</span>
        <textarea name="notes" class="textarea textarea-bordered" rows="4">{{ old('notes', $payment->notes) }}</textarea>
    </label>
</div>
