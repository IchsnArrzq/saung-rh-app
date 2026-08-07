<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Tambah Akun Admin / Kasir</h2>
    </x-slot>

    @include('admin.partials.flash')

    <x-card class="mt-5">
        <form method="POST" action="{{ route('admin-users.store') }}" class="space-y-5">
            @csrf
            
            @include('admin.admin-users._form')

            <x-form-actions submit-label="Simpan Akun" :cancel-href="route('admin-users.index')" />
        </form>
    </x-card>
</x-admin-layout>
