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

        $redirectTo = match (true) {
            $user?->hasRole('cashier') => route('pos.order.index', absolute: false),
            $user?->hasRole('manager') => route('manager.dashboard', absolute: false),
            $user?->hasRole('receptionist') => route('receptionist.dashboard', absolute: false),
            $user?->hasRole('waiter') => route('waiter.dashboard', absolute: false),
            $user?->hasRole('chef') => route('kds.index', absolute: false),
            $user?->hasRole('ob') => route('ob.dashboard', absolute: false),
            $user?->hasAnyRole(['superadmin', 'admin']) => route('dashboard', absolute: false),
            default => route('customer.dashboard', absolute: false),
        };

        $intended = (string) session()->get('url.intended', '');
        $intendedPath = (string) parse_url($intended, PHP_URL_PATH);
        $intendedIsCustomer = str_starts_with($intendedPath, '/customer');
        $intendedIsStaff = (bool) preg_match('#^/(admin|manager|receptionist|waiter|ob)#', $intendedPath);

        // Prevent role mismatch redirect loops that end in 403 pages.
        // Staf tidak boleh diarahkan ke portal customer, dan sebaliknya.
        if ($intendedPath !== '' && (($isStaff && $intendedIsCustomer) || (! $isStaff && $intendedIsStaff))) {
            session()->forget('url.intended');
        }

        $this->redirectIntended(default: $redirectTo, navigate: true);
    }
}; ?>

<div>
    <!-- Heading -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-900">Selamat datang kembali 👋</h1>
        <p class="mt-1 text-sm text-stone-500">Masuk untuk melanjutkan ke akun Anda.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold" />
            <x-text-input wire:model.blur="form.email" id="email" class="block mt-1 w-full" type="email" name="email"
                required autofocus autocomplete="username" placeholder="email@example.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="font-bold" />
            <x-password-input wire:model.blur="form.password" id="password" class="block mt-1 w-full"
                name="password" required autocomplete="current-password" placeholder="Masukkan password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me + Forgot password -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="checkbox checkbox-sm checkbox-primary rounded-md border"
                    name="remember">
                <span class="ms-2 text-sm text-stone-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-medium text-stone-600 transition hover:text-primary hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-100"
                    href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center" wire:target="login" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">{{ __('Masuk') }}</span>
            <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> {{ __('Memproses...') }}
            </span>
        </x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-stone-600">
            {{ __('Belum punya akun?') }}
            <a href="{{ route('register') }}" wire:navigate
                class="font-semibold text-primary transition hover:underline">{{ __('Daftar sekarang') }}</a>
        </p>
    @endif
</div>
