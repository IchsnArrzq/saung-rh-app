<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Tambah Customer Baru</h2>
    </x-slot>

    @include('admin.partials.flash')

    <x-card class="mt-5">
        <form method="POST" action="{{ route('customer-users.store') }}" class="space-y-5">
            @csrf
            
            @include('admin.customer-users._form')

            <x-form-actions submit-label="Simpan Customer" :cancel-href="route('customer-users.index')" />
        </form>
    </x-card>
</x-admin-layout>
