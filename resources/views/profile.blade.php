@php
    // Akun yang memegang peran staf dikelola admin di halaman Karyawan dan tidak
    // menghapus dirinya sendiri (dijaga juga di delete-user-form).
    $profileUser = auth()->user();
    $canDeleteSelf = ! $profileUser->roles->contains(fn ($role) => $role->name !== 'customer');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Profil saya</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-6">
        <x-card title="Foto profil" description="Tampil di menu akun dan di daftar karyawan.">
            <livewire:profile.update-profile-photo-form />
        </x-card>

        <x-card title="Data diri" description="Nama dan kontak yang terlihat oleh admin dan rekan kerja.">
            <livewire:profile.update-profile-information-form />
        </x-card>

        <x-card title="Kata sandi" description="Pakai kata sandi panjang yang tidak Anda pakai di aplikasi lain.">
            <livewire:profile.update-password-form />
        </x-card>

        @if ($canDeleteSelf)
            <x-card title="Hapus akun">
                <livewire:profile.delete-user-form />
            </x-card>
        @else
            <p class="text-sm text-base-content/60">
                Akun karyawan tidak dihapus dari sini — admin menonaktifkan atau menghapusnya di halaman Karyawan.
            </p>
        @endif
    </div>
</x-app-layout>
