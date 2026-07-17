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
    <!-- Heading -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-900">Verifikasi email Anda</h1>
        <p class="mt-1 text-sm text-stone-500">
            Terima kasih telah mendaftar! Sebelum mulai, silakan verifikasi email Anda melalui tautan yang baru saja kami kirim. Jika belum menerimanya, kami dengan senang hati mengirim ulang.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4 text-sm">
            {{ __('Tautan verifikasi baru telah dikirim ke email yang Anda gunakan saat mendaftar.') }}
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <x-primary-button class="w-full justify-center" wire:click="sendVerification" wire:target="sendVerification" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="sendVerification">{{ __('Kirim ulang email verifikasi') }}</span>
            <span wire:loading wire:target="sendVerification" class="inline-flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> {{ __('Mengirim...') }}
            </span>
        </x-primary-button>

        <button wire:click="logout" type="submit"
            class="rounded-md text-sm font-medium text-stone-600 underline transition hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-100">
            {{ __('Keluar') }}
        </button>
    </div>
</div>
