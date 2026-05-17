<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Order</legend>
                <select class="select select-bordered w-full" wire:model.defer="order_id" required>
                    <option value="">Pilih order</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}">
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
                <select class="select select-bordered w-full" wire:model.defer="method" required>
                    @foreach ($methodOptions as $methodOption)
                        <option value="{{ $methodOption }}">{{ str_replace('_', ' ', $methodOption) }}</option>
                    @endforeach
                </select>
                @error('method')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Tipe Pembayaran</legend>
                <input type="text" class="input input-bordered w-full" value="FULL" readonly>
                <input type="hidden" wire:model.defer="type">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Status</legend>
                <select class="select select-bordered w-full" wire:model.defer="status" required>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}">{{ ucfirst($statusOption) }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Jumlah</legend>
                <input type="number" step="0.01" min="0" class="input input-bordered w-full" wire:model.defer="amount"
                    required>
                @error('amount')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Referensi</legend>
                <input type="text" class="input input-bordered w-full" wire:model.defer="reference"
                    placeholder="No struk / kode transaksi">
                @error('reference')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Waktu Bayar</legend>
                <input type="datetime-local" class="input input-bordered w-full" wire:model.defer="paid_at">
                @error('paid_at')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset md:col-span-2">
                <legend class="fieldset-legend">Catatan</legend>
                <textarea class="textarea textarea-bordered w-full" rows="4" wire:model.defer="notes"></textarea>
                @error('notes')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </fieldset>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                {{ $payment ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('payments.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
