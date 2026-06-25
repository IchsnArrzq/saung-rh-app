<div class="space-y-4">
    @if (session('success'))
        <div class="alert alert-success py-2 text-sm"><i class="ri-checkbox-circle-line"></i><span>{{ session('success') }}</span></div>
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
                <label class="form-control">
                    <span class="label-text mb-1">Paket</span>
                    <input type="text" wire:model="plan" class="input input-bordered input-sm">
                    @error('plan') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </label>
                <label class="form-control">
                    <span class="label-text mb-1">License Key</span>
                    <input type="text" wire:model="license_key" class="input input-bordered input-sm font-mono">
                    @error('license_key') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </label>
                <label class="form-control">
                    <span class="label-text mb-1">Status</span>
                    <select wire:model="status" class="select select-bordered select-sm">
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-control">
                    <span class="label-text mb-1">Jumlah Seat</span>
                    <input type="number" wire:model="seats" min="1" class="input input-bordered input-sm">
                    @error('seats') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </label>
                <label class="form-control">
                    <span class="label-text mb-1">Berlaku Hingga</span>
                    <input type="date" wire:model="expires_at" class="input input-bordered input-sm">
                </label>
                <label class="form-control sm:col-span-2">
                    <span class="label-text mb-1">Catatan</span>
                    <textarea wire:model="notes" rows="2" class="textarea textarea-bordered textarea-sm"></textarea>
                </label>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ri-save-line"></i> Simpan Lisensi</button>
                </div>
            </form>
        </div>
    </div>
</div>
