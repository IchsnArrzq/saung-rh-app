<div class="space-y-4">
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
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
                            {{-- `step` defaults to 1 on a number input, which would have the
                                 browser reject a decimal tax rate like 12.5 and silently refuse
                                 to submit the whole form. --}}
                            <x-input :label="$keyLabels[$item->key] ?? $item->key"
                                name="values.{{ $item->key }}" size="sm"
                                :type="$item->type === 'number' ? 'number' : 'text'"
                                :step="$item->type === 'number' ? 'any' : null"
                                wire:model="values.{{ $item->key }}" />
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <x-button type="submit" variant="primary" size="sm" icon="ri-save-line">Simpan Pengaturan</x-button>
        </div>
    </form>
</div>
