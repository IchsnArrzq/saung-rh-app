<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Ubah kategori permintaan</h2>
    </x-slot>

    <livewire:admin.special-request-categories.form :category="$specialRequestCategory" />
</x-admin-layout>
