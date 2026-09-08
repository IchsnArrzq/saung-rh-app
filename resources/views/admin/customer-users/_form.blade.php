@php
    $customer = $customer ?? new \App\Models\User();
    $isEdit = isset($customer->id);
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <x-input label="Nama lengkap" name="name" value="{{ old('name', $customer->name) }}" required autofocus
        autocomplete="name" />

    <x-input label="Email" name="email" type="email" value="{{ old('email', $customer->email) }}" required
        autocomplete="username" />

    <x-input label="{{ $isEdit ? 'Password (kosongkan jika tidak diubah)' : 'Password' }}" name="password"
        type="password" autocomplete="new-password" :required="! $isEdit" />

    <x-input label="Konfirmasi password" name="password_confirmation" type="password" autocomplete="new-password"
        :required="! $isEdit" />

    <div class="md:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <x-checkbox size="sm" name="is_active" value="1" label="Akun aktif"
            :checked="(bool) old('is_active', $customer->is_active ?? true)" />
        @error('is_active')
            <p class="mt-1 text-xs text-error">{{ $message }}</p>
        @enderror
    </div>
</div>
