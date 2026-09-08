@php
    $admin_user = $admin_user ?? new \App\Models\User();
    $isEdit = isset($admin_user->id);
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <x-input label="Nama lengkap" name="name" value="{{ old('name', $admin_user->name) }}" required autofocus
        autocomplete="name" />

    <x-input label="Email" name="email" type="email" value="{{ old('email', $admin_user->email) }}" required
        autocomplete="username" />

    @if (! $admin_user->hasRole('superadmin'))
        @php($currentRole = old('role', $admin_user->roles->pluck('name')->first() ?? 'admin'))
        <x-select label="Role" name="role" fieldClass="md:col-span-2" :required="true"
            :options="['admin' => 'Admin', 'cashier' => 'Kasir']" :selected="$currentRole" />
    @endif

    <x-input label="{{ $isEdit ? 'Password (kosongkan jika tidak diubah)' : 'Password' }}" name="password"
        type="password" autocomplete="new-password" :required="! $isEdit" />

    <x-input label="Konfirmasi password" name="password_confirmation" type="password" autocomplete="new-password"
        :required="! $isEdit" />

    <div class="md:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <x-checkbox size="sm" name="is_active" value="1" label="Akun aktif"
            :checked="(bool) old('is_active', $admin_user->is_active ?? true)" />
        @error('is_active')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>
</div>
