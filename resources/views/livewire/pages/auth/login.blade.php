<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();
        $isStaff = $user && ! $user->hasRole('customer');

        // Peran bawaan mendarat di tempat yang sama seperti dulu; peran buatan layar
        // Peran & hak akses mendarat di halaman pertama yang memang boleh dibukanya.
        $redirectTo = \App\Support\PortalHome::url($user);

        $intended = (string) session()->get('url.intended', '');
        $intendedPath = (string) parse_url($intended, PHP_URL_PATH);
        $intendedIsCustomer = str_starts_with($intendedPath, '/customer');
        $intendedIsStaff = (bool) preg_match('#^/(admin|manager|receptionist|waiter|ob|panel-meja|song-queue)#', $intendedPath);

        // Prevent role mismatch redirect loops that end in 403 pages.
        // Staf tidak boleh diarahkan ke portal customer, dan sebaliknya.
        if ($intendedPath !== '' && (($isStaff && $intendedIsCustomer) || (! $isStaff && $intendedIsStaff))) {
            session()->forget('url.intended');
        }

        $this->redirectIntended(default: $redirectTo, navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Selamat datang kembali</h1>
        <p class="mt-1 text-sm text-base-content/60">Masuk untuk melanjutkan ke akun Anda.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <x-input label="Email" name="form.email" type="email" icon="ri-mail-line" wire:model.blur="form.email"
            required autofocus autocomplete="username" placeholder="nama@contoh.com" />

        <x-field label="Kata sandi" name="form.password" for="login-password" required>
            <x-password-input id="login-password" wire:model.blur="form.password" autocomplete="current-password"
                placeholder="Masukkan kata sandi" />
        </x-field>

        <div class="flex items-center justify-between gap-3">
            <x-checkbox name="form.remember" wire:model="form.remember" label="Ingat saya di perangkat ini" size="sm" />

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate
                    class="link link-hover shrink-0 text-sm font-medium text-base-content/70 hover:text-primary">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-button type="submit" variant="primary" :block="true" icon="ri-login-box-line" loading="login" class="min-h-11">
            Masuk
        </x-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-base-content/70">
            Belum punya akun?
            <a href="{{ route('register') }}" wire:navigate class="link link-hover font-semibold text-primary">Daftar sekarang</a>
        </p>
    @endif
</div>
