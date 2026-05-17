@php
    $customer = $customer ?? new \App\Models\User();
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Nama Lengkap</legend>
        <input type="text" name="name" class="input input-bordered w-full" value="{{ old('name', $customer->name) }}" required autofocus autocomplete="name">
        @error('name')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Email</legend>
        <input type="email" name="email" class="input input-bordered w-full" value="{{ old('email', $customer->email) }}" required autocomplete="username">
        @error('email')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">{{ isset($customer->id) ? 'Password (Kosongkan jika tidak diubah)' : 'Password' }}</legend>
        <input type="password" name="password" class="input input-bordered w-full" autocomplete="new-password" {{ isset($customer->id) ? '' : 'required' }}>
        @error('password')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset">
        <legend class="fieldset-legend">Konfirmasi Password</legend>
        <input type="password" name="password_confirmation" class="input input-bordered w-full" autocomplete="new-password" {{ isset($customer->id) ? '' : 'required' }}>
        @error('password_confirmation')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="fieldset md:col-span-2">
        <legend class="fieldset-legend">Status Akun</legend>
        <label class="label cursor-pointer justify-start gap-3 px-0">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-sm" @checked((bool) old('is_active', $customer->is_active ?? true))>
            <span class="label-text">Akun Aktif</span>
        </label>
        @error('is_active')
            <p class="label text-error">{{ $message }}</p>
        @enderror
    </fieldset>
</div>
