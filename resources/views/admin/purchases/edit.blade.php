<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-ghost">
                <i class="ri-arrow-left-line"></i>
            </a>
            <h2 class="text-xl font-semibold">Pembelian: {{ $purchase->code }}</h2>
        </div>
    </x-slot>

    <livewire:admin.purchases.form :purchase="$purchase" />
</x-admin-layout>
