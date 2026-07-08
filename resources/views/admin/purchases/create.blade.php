<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-ghost">
                <i class="ri-arrow-left-line"></i>
            </a>
            <h2 class="text-xl font-semibold">Buat Pembelian</h2>
        </div>
    </x-slot>

    <livewire:admin.purchases.form />
</x-admin-layout>
