<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Kategori Meja</h2>
    </x-slot>

    <livewire:admin.table-categories.form :table-category="$tableCategory" />
</x-app-layout>
