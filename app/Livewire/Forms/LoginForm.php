<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string|email', message: [
        'required' => 'Masukkan email akun Anda.',
        'email' => 'Format email belum benar, mis. nama@contoh.com.',
    ])]
    public string $email = '';

    #[Validate('required|string', message: ['required' => 'Masukkan kata sandi Anda.'])]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * Akun yang dinonaktifkan di halaman Karyawan tidak bisa masuk: `is_active`
     * ikut jadi syarat attempt(). Kata sandinya tetap dicek lebih dulu, jadi pesan
     * "dinonaktifkan" hanya muncul untuk pemilik kata sandi yang benar — orang lain
     * tetap hanya melihat "email atau kata sandi salah".
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only(['email', 'password']);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => Auth::validate($credentials)
                    ? 'Akun ini sedang dinonaktifkan. Hubungi admin restoran untuk mengaktifkannya lagi.'
                    : 'Email atau kata sandi salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => 'Terlalu banyak percobaan masuk. Coba lagi dalam '.$seconds.' detik.',
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
