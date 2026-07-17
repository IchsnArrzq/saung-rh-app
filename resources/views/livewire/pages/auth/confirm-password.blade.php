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
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Heading -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-900">Konfirmasi password</h1>
        <p class="mt-1 text-sm text-stone-500">
            Ini adalah area aman. Silakan konfirmasi password Anda sebelum melanjutkan.
        </p>
    </div>

    <form wire:submit="confirmPassword" class="space-y-4">
        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="font-bold" />
            <x-password-input wire:model="password" id="password" class="block mt-1 w-full"
                name="password" required autocomplete="current-password" placeholder="Masukkan password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center" wire:target="confirmPassword" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="confirmPassword">{{ __('Konfirmasi') }}</span>
            <span wire:loading wire:target="confirmPassword" class="inline-flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> {{ __('Memproses...') }}
            </span>
        </x-primary-button>
    </form>
</div>
