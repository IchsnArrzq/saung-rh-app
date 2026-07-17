<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink($this->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <!-- Heading -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-900">Lupa password?</h1>
        <p class="mt-1 text-sm text-stone-500">
            Tidak masalah. Masukkan email Anda dan kami akan mengirim tautan untuk mengatur ulang password.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-4">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold" />
            <x-text-input wire:model.blur="email" id="email" class="block mt-1 w-full" type="email" name="email" required
                autofocus placeholder="email@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center" wire:target="sendPasswordResetLink" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="sendPasswordResetLink">{{ __('Kirim tautan reset') }}</span>
            <span wire:loading wire:target="sendPasswordResetLink" class="inline-flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> {{ __('Mengirim...') }}
            </span>
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-600">
        <a href="{{ route('login') }}" wire:navigate
            class="inline-flex items-center gap-1 font-semibold text-primary transition hover:underline">
            <i class="ri-arrow-left-line"></i> {{ __('Kembali ke halaman masuk') }}</a>
    </p>
</div>
