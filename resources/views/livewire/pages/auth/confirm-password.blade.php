<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Masukkan kata sandi Anda.',
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => 'Kata sandi tidak cocok.',
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Konfirmasi kata sandi</h1>
        <p class="mt-1 text-sm text-base-content/60">
            Halaman berikutnya berisi pengaturan penting. Masukkan kata sandi Anda sekali lagi untuk melanjutkan.
        </p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-4">
        <x-field label="Kata sandi" name="password" for="confirm-password" required>
            <x-password-input id="confirm-password" wire:model="password" autocomplete="current-password" autofocus />
        </x-field>

        <x-button type="submit" variant="primary" :block="true" icon="ri-shield-check-line" loading="confirmPassword"
            class="min-h-11">
            Konfirmasi
        </x-button>
    </form>
</div>
