<div class="space-y-4">
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    @php
        $alert = match ($summary['state']) {
            'expired' => 'alert-error',
            'expiring' => 'alert-warning',
            'none' => 'alert-info',
            default => 'alert-success',
        };
    @endphp
    <div class="alert {{ $alert }}">
        <i class="ri-shield-keyhole-line text-xl"></i>
        <div>
            <div class="font-semibold">{{ $summary['label'] }}</div>
            @if ($summary['plan'])<div class="text-xs opacity-80">Paket: {{ ucfirst($summary['plan']) }}</div>@endif
        </div>
    </div>

    <div class="card border border-base-300 bg-base-100 rounded-xl">
        <div class="card-body gap-3">
            <h3 class="card-title text-base"><i class="ri-key-2-line text-primary"></i> Detail Lisensi</h3>
            <form wire:submit="save" class="grid gap-3 sm:grid-cols-2">
                <x-input label="Paket" name="plan" size="sm" wire:model="plan" />

                <x-input label="License Key" name="license_key" size="sm" class="font-mono"
                    wire:model="license_key" />

                <x-select label="Status" name="status" size="sm" wire:model="status">
                    @foreach ($statuses as $st)
                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                    @endforeach
                </x-select>

                <x-input label="Jumlah Seat" name="seats" type="number" size="sm" min="1" wire:model="seats" />

                <x-input label="Berlaku Hingga" name="expires_at" type="date" size="sm" wire:model="expires_at" />

                <x-textarea field-class="sm:col-span-2" label="Catatan" name="notes" size="sm" :rows="2"
                    wire:model="notes" />

                <div class="sm:col-span-2 flex justify-end">
                    <x-button type="submit" variant="primary" size="sm" icon="ri-save-line">Simpan Lisensi</x-button>
                </div>
            </form>
        </div>
    </div>
</div>
