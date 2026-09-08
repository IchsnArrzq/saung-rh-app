<div>
    {{-- Halaman ini belum punya isi. Yang dirender adalah pesan untuk pengguna, bukan
         catatan rencana untuk diri sendiri. --}}
    <x-empty-state icon="ri-tools-line" title="Detail pesanan kasir belum tersedia"
        description="Rincian pesanan bisa dilihat dari daftar pesanan kasir.">
        <x-slot:actions>
            <x-button variant="primary" size="sm" :href="route('pos.order.index')" wire:navigate>
                Ke daftar pesanan
            </x-button>
        </x-slot:actions>
    </x-empty-state>
</div>
