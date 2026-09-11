<x-error-page code="419" title="Halaman sudah kedaluwarsa" icon="ri-time-line" tone="warning">
    Halaman ini terlalu lama terbuka tanpa aktivitas, jadi kirimannya kami tolak demi keamanan. Kembali ke halaman
    sebelumnya, muat ulang, lalu kirim sekali lagi.

    <x-slot:actions>
        <x-button variant="primary" icon="ri-arrow-left-line" onclick="history.length > 1 ? history.back() : location.assign('/')">
            Kembali ke halaman sebelumnya
        </x-button>
        <x-button variant="ghost" icon="ri-home-4-line" :href="route('public.home')">Ke beranda</x-button>
    </x-slot:actions>
</x-error-page>
