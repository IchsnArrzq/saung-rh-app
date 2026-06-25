<div class="space-y-4">
    @if (session('success'))
        <div class="alert alert-success py-2 text-sm"><i class="ri-checkbox-circle-line"></i><span>{{ session('success') }}</span></div>
    @endif

    @php
        $groupLabels = ['profile' => 'Profil Bisnis', 'finance' => 'Keuangan', 'social' => 'Media Sosial', 'general' => 'Umum'];
        $keyLabels = [
            'app.name' => 'Nama Aplikasi', 'app.tagline' => 'Tagline',
            'contact.address' => 'Alamat', 'contact.phone' => 'Telepon', 'contact.email' => 'Email',
            'finance.currency' => 'Mata Uang', 'finance.tax_percent' => 'Pajak (%)',
            'finance.service_charge_percent' => 'Service Charge (%)', 'social.instagram' => 'Instagram',
        ];
    @endphp

    <form wire:submit="save" class="space-y-4">
        @foreach ($groups as $group => $items)
            <div class="card border border-base-300 bg-base-100 rounded-xl">
                <div class="card-body gap-3">
                    <h3 class="card-title text-base"><i class="ri-settings-3-line text-primary"></i> {{ $groupLabels[$group] ?? ucfirst($group) }}</h3>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($items as $item)
                            <label class="form-control">
                                <span class="label-text mb-1">{{ $keyLabels[$item->key] ?? $item->key }}</span>
                                <input type="{{ $item->type === 'number' ? 'number' : 'text' }}"
                                    wire:model="values.{{ $item->key }}" class="input input-bordered input-sm">
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-save-line"></i> Simpan Pengaturan</button>
        </div>
    </form>
</div>
