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
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar, mis. nama@contoh.com.',
            'email.unique' => 'Email ini sudah terdaftar. Masuk saja, atau pakai email lain.',
            'password.required' => 'Buat kata sandi untuk akun Anda.',
            'password.confirmed' => 'Ulangan kata sandi belum sama.',
            'password.min' => 'Kata sandi minimal :min karakter.',
        ];
    }

    /**
     * Validate a single field as the user leaves it (wire:model.blur).
     */
    public function updated(string $property): void
    {
        if ($property === 'email') {
            // Dinormalkan dulu: aturan `lowercase` jadi tidak pernah menolak tamu
            // hanya karena ponselnya menulis huruf besar di awal email.
            $this->email = mb_strtolower(trim($this->email));
        }

        $this->validateOnly($property);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->email = mb_strtolower(trim($this->email));

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
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Buat akun baru</h1>
        <p class="mt-1 text-sm text-base-content/60">Daftar untuk memesan dan memantau pesanan Anda dengan lebih mudah.</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <x-input label="Nama lengkap" name="name" icon="ri-user-line" wire:model.blur="name" required autofocus
            autocomplete="name" placeholder="Nama Anda" />

        <x-input label="Email" name="email" type="email" icon="ri-mail-line" wire:model.blur="email" required
            autocomplete="username" placeholder="nama@contoh.com" />

        <x-field label="Kata sandi" name="password" for="register-password" required hint="Minimal 8 karakter.">
            <x-password-input id="register-password" wire:model.blur="password" autocomplete="new-password" />
        </x-field>

        <x-field label="Ulangi kata sandi" name="password_confirmation" for="register-password-confirmation" required>
            <x-password-input id="register-password-confirmation" wire:model.blur="password_confirmation"
                autocomplete="new-password" />
        </x-field>

        <x-button type="submit" variant="primary" :block="true" icon="ri-user-add-line" loading="register" class="min-h-11">
            Daftar
        </x-button>
    </form>

    <p class="mt-6 text-center text-sm text-base-content/70">
        Sudah punya akun?
        <a href="{{ route('login') }}" wire:navigate class="link link-hover font-semibold text-primary">Masuk di sini</a>
    </p>
</div>
