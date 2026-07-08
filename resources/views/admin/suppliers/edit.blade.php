<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Supplier</h2>
    </x-slot>

    <livewire:admin.suppliers.form :supplier="$supplier" />
</x-admin-layout>
