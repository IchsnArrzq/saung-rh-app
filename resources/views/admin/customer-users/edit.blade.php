<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Edit Customer: {{ $customer->name }}</h2>
    </x-slot>

    @include('admin.partials.flash')

    <x-card class="mt-5">
        <form method="POST" action="{{ route('customer-users.update', $customer) }}" class="space-y-5">
            @csrf
            @method('PUT')
            
            @include('admin.customer-users._form')

            <x-form-actions submit-label="Update Customer" :cancel-href="route('customer-users.index')" />
        </form>
    </x-card>
</x-admin-layout>
