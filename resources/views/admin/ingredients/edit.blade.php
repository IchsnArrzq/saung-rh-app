<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Bahan</h2>
    </x-slot>

    <livewire:admin.ingredients.form :ingredient="$ingredient" />
</x-admin-layout>
