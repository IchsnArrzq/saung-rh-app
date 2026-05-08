<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Reservasi</h2>
    </x-slot>

    <livewire:admin.reservations.form :reservation="$reservation" />
</x-app-layout>
