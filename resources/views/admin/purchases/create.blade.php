<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <x-button variant="ghost" size="sm" shape="square" icon="ri-arrow-left-line"
                label="Kembali" :href="route('purchases.index')" />
            <h2 class="text-xl font-semibold">Buat Pembelian</h2>
        </div>
    </x-slot>

    <livewire:admin.purchases.form />
</x-admin-layout>
