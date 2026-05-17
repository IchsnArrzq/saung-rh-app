<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Status Menu</h2>
    </x-slot>

    <livewire:admin.menu-statuses.form :menu-status="$menuStatus" />
</x-app-layout>
