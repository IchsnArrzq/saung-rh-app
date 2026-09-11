<x-error-page code="503" title="Sedang dalam pemeliharaan" icon="ri-hammer-line" tone="info">
    Kami sedang merapikan sistem sebentar. Silakan kembali beberapa saat lagi.

    <x-slot:actions>
        <x-button variant="primary" icon="ri-refresh-line" onclick="location.reload()">Muat ulang</x-button>
    </x-slot:actions>
</x-error-page>
