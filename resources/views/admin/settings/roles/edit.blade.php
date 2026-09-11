<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Peran: {{ $role->label() }}</h2>
    </x-slot>

    <livewire:admin.roles.form :role="$role" />
</x-admin-layout>
