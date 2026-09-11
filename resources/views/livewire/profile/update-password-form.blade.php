<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ], [
                'current_password.required' => 'Masukkan kata sandi Anda yang sekarang.',
                'current_password.current_password' => 'Kata sandi sekarang tidak cocok.',
                'password.required' => 'Masukkan kata sandi baru.',
                'password.confirmed' => 'Ulangan kata sandi baru belum sama.',
                'password.min' => 'Kata sandi baru minimal :min karakter.',
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<form wire:submit="updatePassword" class="space-y-4">
    <x-field label="Kata sandi sekarang" name="current_password" for="profile-current-password" required>
        <x-password-input id="profile-current-password" wire:model="current_password" autocomplete="current-password" />
    </x-field>

    <div class="grid gap-4 md:grid-cols-2">
        <x-field label="Kata sandi baru" name="password" for="profile-new-password" required hint="Minimal 8 karakter.">
            <x-password-input id="profile-new-password" wire:model="password" autocomplete="new-password" />
        </x-field>

        <x-field label="Ulangi kata sandi baru" name="password_confirmation" for="profile-new-password-confirmation" required>
            <x-password-input id="profile-new-password-confirmation" wire:model="password_confirmation"
                autocomplete="new-password" />
        </x-field>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <p x-data="{ shown: false, timer: null }"
            x-on:password-updated.window="shown = true; clearTimeout(timer); timer = setTimeout(() => shown = false, 2500)"
            x-show="shown" x-transition.opacity.duration.200ms style="display: none"
            class="inline-flex items-center gap-1 text-sm text-success" role="status">
            <i class="ri-checkbox-circle-line" aria-hidden="true"></i> Kata sandi diganti.
        </p>

        <x-button type="submit" variant="outline" icon="ri-lock-password-line" loading="updatePassword">
            Ganti kata sandi
        </x-button>
    </div>
</form>
