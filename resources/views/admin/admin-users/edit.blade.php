@php
    // Akun Superadmin hanya bisa disunting oleh Superadmin — sama dengan aturan
    // di SaveStaffUserUseCase; di sini supaya formnya tidak ditawarkan sama sekali.
    $locked = $admin_user->hasRole(\App\Models\Role::LOCKED) && ! auth()->user()->hasRole(\App\Models\Role::LOCKED);
@endphp

<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Ubah akun: {{ $admin_user->name }}</h2>
    </x-slot>

    @if ($locked)
        <div class="space-y-4">
            <x-alert type="warning" icon="ri-shield-star-line" title="Akun Superadmin dilindungi">
                Hanya Superadmin yang bisa mengubah akun ini.
            </x-alert>
            <x-button variant="ghost" icon="ri-arrow-left-line" :href="route('admin-users.index')">
                Kembali ke daftar karyawan
            </x-button>
        </div>
    @else
        <form method="POST" action="{{ route('admin-users.update', $admin_user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('admin.partials.flash')
            @include('admin.admin-users._form')

            <x-form-actions submit-label="Simpan perubahan" :cancel-href="route('admin-users.index')" />
        </form>
    @endif
</x-admin-layout>
