<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'Masukkan email akun Anda.',
            'email.email' => 'Format email belum benar, mis. nama@contoh.com.',
            'password.required' => 'Buat kata sandi baru.',
            'password.confirmed' => 'Ulangan kata sandi belum sama.',
            'password.min' => 'Kata sandi minimal :min karakter.',
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Buat kata sandi baru</h1>
        <p class="mt-1 text-sm text-base-content/60">Setelah tersimpan, masuk dengan kata sandi yang baru.</p>
    </div>

    <form wire:submit="resetPassword" class="space-y-4">
        <x-input label="Email" name="email" type="email" icon="ri-mail-line" wire:model="email" required autofocus
            autocomplete="username" />

        <x-field label="Kata sandi baru" name="password" for="reset-password" required hint="Minimal 8 karakter.">
            <x-password-input id="reset-password" wire:model="password" autocomplete="new-password" />
        </x-field>

        <x-field label="Ulangi kata sandi baru" name="password_confirmation" for="reset-password-confirmation" required>
            <x-password-input id="reset-password-confirmation" wire:model="password_confirmation" autocomplete="new-password" />
        </x-field>

        <x-button type="submit" variant="primary" :block="true" icon="ri-lock-password-line" loading="resetPassword"
            class="min-h-11">
            Simpan kata sandi baru
        </x-button>
    </form>
</div>
