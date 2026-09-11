<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Verifikasi email Anda</h1>
        <p class="mt-1 text-sm text-base-content/60">
            Kami sudah mengirim tautan verifikasi ke email Anda. Buka tautan itu untuk mulai memakai akun.
            Belum menerima? Kirim ulang dari sini.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-alert type="success" class="mb-4">
            Tautan verifikasi baru sudah dikirim ke email yang Anda pakai saat mendaftar.
        </x-alert>
    @endif

    <div class="flex flex-col gap-3">
        <x-button variant="primary" :block="true" icon="ri-mail-send-line" wire:click="sendVerification"
            loading="sendVerification" class="min-h-11">
            Kirim ulang email verifikasi
        </x-button>

        <x-button variant="ghost" :block="true" icon="ri-logout-box-r-line" wire:click="logout" loading="logout">
            Keluar
        </x-button>
    </div>
</div>
