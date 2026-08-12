<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button variant="ghost" size="sm" shape="square" icon="ri-arrow-left-line"
                label="Kembali" :href="route('sales.index')" />
            <h2 class="text-xl font-semibold">Penjualan: {{ $sale->code }}</h2>
        </div>
    </x-slot>

    <livewire:admin.sales.form :sale="$sale" />
</x-admin-layout>
