@php
    // Header Retry-After diisi oleh throttle middleware — angka nyata, bukan tebakan.
    $retryAfter = isset($exception) && method_exists($exception, 'getHeaders')
        ? ($exception->getHeaders()['Retry-After'] ?? null)
        : null;
@endphp

<x-error-page code="429" title="Terlalu banyak percobaan" icon="ri-hourglass-line" tone="warning">
    Ada terlalu banyak permintaan dalam waktu singkat.
    @if (is_numeric($retryAfter))
        Coba lagi dalam <span class="tabular-nums">{{ (int) $retryAfter }}</span> detik.
    @else
        Tunggu sebentar, lalu coba lagi.
    @endif

    <x-slot:actions>
        <x-button variant="primary" icon="ri-refresh-line" onclick="location.reload()">Coba lagi</x-button>
        <x-button variant="ghost" icon="ri-arrow-left-line" onclick="history.length > 1 ? history.back() : location.assign('/')">
            Kembali
        </x-button>
    </x-slot:actions>
</x-error-page>
