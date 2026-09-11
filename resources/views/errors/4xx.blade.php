@php
    $status = isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400;
@endphp

<x-error-page :code="$status" title="Permintaan tidak bisa diproses" icon="ri-error-warning-line" tone="warning"
    :exception="$exception ?? null">
    Ada yang tidak sesuai dengan permintaan ini. Kembali ke halaman sebelumnya lalu coba lagi.

    <x-slot:actions>
        <x-button variant="primary" icon="ri-arrow-left-line" onclick="history.length > 1 ? history.back() : location.assign('/')">
            Kembali
        </x-button>
        <x-button variant="ghost" icon="ri-home-4-line" :href="route('public.home')">Ke beranda</x-button>
    </x-slot:actions>
</x-error-page>
