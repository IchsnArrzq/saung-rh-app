<div wire:key="special-request-form">
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-semibold"><i class="ri-customer-service-2-line text-accent"></i> Permintaan Khusus</span>
    </div>

    @if (! $sessionId)
        <div class="alert alert-info text-sm"><i class="ri-qr-scan-2-line"></i><span>Scan QR meja untuk kirim permintaan.</span></div>
    @else
        @if (session('special_status'))
            <div class="alert alert-success py-1.5 text-xs mb-2"><span>{{ session('special_status') }}</span></div>
        @endif

        <form wire:submit="submit" class="space-y-2">
            <x-select :bare="true" label="Kategori permintaan" name="category" size="sm" class="w-full"
                wire:model="category" :options="$categories" />
            <textarea wire:model="description" rows="2" maxlength="280"
                placeholder="Mis. tolong siapkan kue ulang tahun, atau kecilkan AC..."
                class="textarea textarea-bordered textarea-sm w-full"></textarea>
            @error('description') <span class="text-error text-xs">{{ $message }}</span> @enderror
            <x-button type="submit" variant="accent" size="sm" :block="true" icon="ri-send-plane-2-line" loading="submit">
                Kirim Permintaan
            </x-button>
        </form>

        @if ($mine->isNotEmpty())
            <ul class="mt-3 space-y-1.5">
                @foreach ($mine as $req)
                    <li class="flex items-center justify-between gap-2 text-sm rounded-lg bg-base-200/60 px-3 py-1.5">
                        <span class="truncate">{{ $req->description }}</span>
                        <x-status-badge :status="$req->status" size="sm" class="shrink-0" />
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
