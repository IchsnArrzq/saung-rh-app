<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Tambah karyawan</h2>
    </x-slot>

    <form method="POST" action="{{ route('admin-users.store') }}" class="space-y-6">
        @csrf

        @include('admin.partials.flash')
        @include('admin.admin-users._form')

        <x-form-actions submit-label="Tambah karyawan" :cancel-href="route('admin-users.index')" />
    </form>
</x-admin-layout>
