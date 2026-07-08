<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.index') }}" class="btn btn-sm btn-ghost">
                <i class="ri-arrow-left-line"></i>
            </a>
            <h2 class="text-xl font-semibold">Penjualan: {{ $sale->code }}</h2>
        </div>
    </x-slot>

    <livewire:admin.sales.form :sale="$sale" />
</x-admin-layout>
