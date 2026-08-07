<div class="space-y-5">
    @include('admin.partials.flash')

    <form wire:submit="save" class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <x-select field-class="md:col-span-2" label="Order" name="order_id" :required="true"
                placeholder="Pilih order" wire:model.defer="order_id" required>
                @foreach ($orders as $order)
                    <option value="{{ $order->id }}">
                        {{ $order->order_number }} - Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                    </option>
                @endforeach
            </x-select>

            <x-select label="Metode" name="method" :required="true" wire:model.defer="method" required>
                @foreach ($methodOptions as $methodOption)
                    <option value="{{ $methodOption->value }}">{{ $methodOption->label() }}</option>
                @endforeach
            </x-select>

            <x-field label="Tipe Pembayaran">
                <input type="text" class="input input-bordered w-full" value="FULL" readonly>
                <input type="hidden" wire:model.defer="type">
            </x-field>

            <x-select label="Status" name="status" :required="true" wire:model.defer="status" required>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                @endforeach
            </x-select>

            <x-input label="Jumlah" name="amount" type="number" step="0.01" min="0" :required="true"
                wire:model.defer="amount" required />

            <x-input label="Referensi" name="reference" wire:model.defer="reference"
                placeholder="No struk / kode transaksi" />

            <x-input label="Waktu Bayar" name="paid_at" type="datetime-local" wire:model.defer="paid_at" />

            <x-textarea field-class="md:col-span-2" label="Catatan" name="notes" :rows="4"
                wire:model.defer="notes" />
        </div>

        <x-form-actions :submit-label="$payment ? 'Update' : 'Simpan'" :cancel-href="route('payments.index')"
            loading="save" />
    </form>
</div>
