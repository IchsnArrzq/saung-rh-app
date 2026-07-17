<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Validation rules shared by real-time (blur) and submit validation.
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Validate a single field as the user leaves it (wire:model.blur).
     */
    public function updated(string $property): void
    {
        $this->validateOnly($property);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate();

        $validated['password'] = Hash::make($validated['password']);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create($validated);

            $customerRole = Role::query()->firstOrCreate([
                'name' => 'customer',
                'guard_name' => 'web',
            ]);

            $user->assignRole($customerRole);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('customer.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Heading -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-900">Buat akun baru</h1>
        <p class="mt-1 text-sm text-stone-500">Daftar untuk mulai memesan dengan lebih mudah.</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nama lengkap')" class="font-bold" />
            <x-text-input wire:model.blur="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="Nama Anda" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold" />
            <x-text-input wire:model.blur="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" placeholder="email@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="font-bold" />
            <x-password-input wire:model.blur="password" id="password" class="block mt-1 w-full"
                name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="font-bold" />
            <x-password-input wire:model.blur="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center" wire:target="register" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="register">{{ __('Daftar') }}</span>
            <span wire:loading wire:target="register" class="inline-flex items-center gap-2">
                <span class="loading loading-spinner loading-xs"></span> {{ __('Memproses...') }}
            </span>
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-600">
        {{ __('Sudah punya akun?') }}
        <a href="{{ route('login') }}" wire:navigate
            class="font-semibold text-primary transition hover:underline">{{ __('Masuk di sini') }}</a>
    </p>
</div>
