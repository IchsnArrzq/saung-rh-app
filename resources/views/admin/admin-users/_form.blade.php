@php
    $admin_user = $admin_user ?? new \App\Models\User();
    $isEdit = $admin_user->exists;
    $isSuperadminAccount = $isEdit && $admin_user->hasRole(\App\Models\Role::LOCKED);
    $isSelf = $isEdit && $admin_user->is(auth()->user());
    $currentRole = old('role', $isEdit ? $admin_user->roles->pluck('name')->first() : null);
@endphp

<x-card title="Data karyawan">
    <div class="grid gap-4 md:grid-cols-2">
        <x-input label="Nama lengkap" name="name" :value="old('name', $admin_user->name)" required autofocus
            autocomplete="name" />

        <x-input label="Nomor HP" name="phone" type="tel" inputmode="numeric" :value="old('phone', $admin_user->phone)"
            autocomplete="tel" hint="Opsional." />

        <x-input label="Email" name="email" type="email" :value="old('email', $admin_user->email)" required
            autocomplete="username" hint="Dipakai untuk masuk ke aplikasi." field-class="md:col-span-2" />
    </div>
</x-card>

<x-card title="Peran & status" description="Peran menentukan halaman dan data yang boleh dibuka karyawan ini.">
    <div class="space-y-4">
        @if ($isSuperadminAccount)
            <x-alert type="warning" icon="ri-shield-star-line">
                Akun Superadmin: perannya tidak bisa diganti dan akunnya selalu aktif.
            </x-alert>
            <input type="hidden" name="is_active" value="1">
        @elseif ($isSelf)
            <div>
                <p class="text-sm text-base-content/70">Peran</p>
                <p class="mt-1 font-medium">{{ \App\Models\Role::labelFor((string) $currentRole) }}</p>
                <p class="mt-1 text-xs text-base-content/60">
                    Anda tidak bisa mengganti peran atau menonaktifkan akun Anda sendiri.
                </p>
            </div>
            <input type="hidden" name="role" value="{{ $currentRole }}">
            <input type="hidden" name="is_active" value="1">
        @else
            <x-select label="Peran" name="role" :options="$roleOptions" :selected="$currentRole" placeholder="Pilih peran"
                required />

            @can('viewAny', \App\Models\Role::class)
                <p class="text-sm text-base-content/70">
                    Hak akses tiap peran diatur di
                    <a href="{{ route('settings.roles-permissions') }}" class="link link-primary">Peran &amp; hak akses</a>.
                </p>
            @endcan

            <div>
                <input type="hidden" name="is_active" value="0">
                <x-checkbox name="is_active" value="1" label="Akun aktif"
                    hint="Akun nonaktif tidak bisa masuk sampai diaktifkan lagi."
                    :checked="(bool) old('is_active', $isEdit ? $admin_user->is_active : true)" />
            </div>
        @endif
    </div>
</x-card>

<x-card :title="$isEdit ? 'Ganti kata sandi' : 'Kata sandi'"
    :description="$isEdit ? 'Kosongkan kalau tidak ingin mengganti kata sandi.' : 'Sampaikan kata sandi ini ke karyawan secara langsung.'">
    <div class="grid gap-4 md:grid-cols-2">
        <x-field :label="$isEdit ? 'Kata sandi baru' : 'Kata sandi'" name="password" for="f-password"
            :required="! $isEdit" hint="Minimal 8 karakter.">
            <x-password-input id="f-password" name="password" autocomplete="new-password" :required="! $isEdit" />
        </x-field>

        <x-field label="Ulangi kata sandi" name="password_confirmation" for="f-password-confirmation"
            :required="! $isEdit">
            <x-password-input id="f-password-confirmation" name="password_confirmation" autocomplete="new-password"
                :required="! $isEdit" />
        </x-field>
    </div>
</x-card>
