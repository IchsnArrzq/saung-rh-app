<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Status Meja</h2>
    </x-slot>

    <livewire:admin.table-statuses.form :table-status="$tableStatus" />
</x-app-layout>
