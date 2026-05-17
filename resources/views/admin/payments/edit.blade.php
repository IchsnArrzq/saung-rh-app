<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Pembayaran</h2>
    </x-slot>

    <livewire:admin.payments.form :payment="$payment" />
</x-app-layout>
