<div>
    {{-- Halaman ini belum punya isi. Yang dirender adalah pesan untuk pengguna, bukan
         catatan rencana untuk diri sendiri; pekerjaan kasir ada di daftar pesanan POS. --}}
    <x-empty-state icon="ri-tools-line" title="Buat pesanan kasir belum tersedia"
        description="Pesanan baru dibuat dari daftar pesanan kasir.">
        <x-slot:actions>
            <x-button variant="primary" size="sm" :href="route('pos.order.index')" wire:navigate>
                Ke daftar pesanan
            </x-button>
        </x-slot:actions>
    </x-empty-state>
</div>
