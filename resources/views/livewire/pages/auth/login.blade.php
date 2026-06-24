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
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold" />
            <x-text-input wire:model="form.email" id="email" class=" block mt-1 w-full" type="email" name="email"
                required autofocus autocomplete="username" placeholder="email@example.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="font-bold" />

            <x-text-input wire:model="form.password" id="password" class=" block mt-1 w-full" type="password"
                name="password" required autocomplete="current-password" placeholder="password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="checkbox checkbox-sm checkbox-primary rounded-md border"
                    name="remember">
                <span class="ms-2 text-sm text-stone-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-stone-600 underline transition hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-base-100"
                    href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div>
