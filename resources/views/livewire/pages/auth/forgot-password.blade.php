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
        ], [
            'email.required' => 'Masukkan email akun Anda.',
            'email.email' => 'Format email belum benar, mis. nama@contoh.com.',
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
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Lupa kata sandi?</h1>
        <p class="mt-1 text-sm text-base-content/60">
            Masukkan email akun Anda. Kami kirimkan tautan untuk membuat kata sandi baru.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-4">
        <x-input label="Email" name="email" type="email" icon="ri-mail-line" wire:model.blur="email" required autofocus
            autocomplete="username" placeholder="nama@contoh.com" />

        <x-button type="submit" variant="primary" :block="true" icon="ri-mail-send-line" loading="sendPasswordResetLink"
            class="min-h-11">
            Kirim tautan
        </x-button>
    </form>

    <p class="mt-6 text-center text-sm text-base-content/70">
        <a href="{{ route('login') }}" wire:navigate class="link link-hover inline-flex items-center gap-1 font-semibold text-primary">
            <i class="ri-arrow-left-line" aria-hidden="true"></i> Kembali ke halaman masuk
        </a>
    </p>
</div>
