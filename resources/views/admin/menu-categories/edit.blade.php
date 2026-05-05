<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Kategori Menu</h2>
    </x-slot>

    <livewire:admin.menu-categories.form :menu-category="$menuCategory" />
</x-app-layout>
