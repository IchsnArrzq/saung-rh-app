<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Order {{ $order->order_number }}</h2>
    </x-slot>

    <livewire:admin.orders.form :order="$order" />
</x-admin-layout>
