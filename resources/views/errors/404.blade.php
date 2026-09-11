<x-error-page code="404" title="Halaman tidak ditemukan" icon="ri-compass-3-line" tone="info" :exception="$exception ?? null">
    Alamatnya mungkin salah ketik, atau halamannya sudah dipindahkan. Kalau Anda datang dari QR meja,
    pindai ulang kode QR di meja Anda.

    <x-slot:actions>
        <x-button variant="primary" icon="ri-home-4-line" :href="route('public.home')">Ke beranda</x-button>
        <x-button variant="outline" icon="ri-book-open-line" :href="route('public.menu')">Lihat menu</x-button>
        <x-button variant="ghost" icon="ri-arrow-left-line" onclick="history.length > 1 ? history.back() : location.assign('/')">
            Kembali
        </x-button>
    </x-slot:actions>
</x-error-page>
