<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Menu</h2>
    </x-slot>

    <livewire:admin.menus.form :menu="$menu" />
</x-admin-layout>
