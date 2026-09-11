@php
    $status = isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
@endphp

<x-error-page :code="$status" title="Layanan sedang bermasalah" icon="ri-tools-line" tone="error">
    Kesalahannya sudah tercatat otomatis. Coba muat ulang sebentar lagi; kalau terus terjadi, beri tahu admin beserta
    waktu kejadian di bawah.

    <x-slot:actions>
        <x-button variant="primary" icon="ri-refresh-line" onclick="location.reload()">Muat ulang</x-button>
        <x-button variant="ghost" icon="ri-home-4-line" :href="route('public.home')">Ke beranda</x-button>
    </x-slot:actions>
</x-error-page>
