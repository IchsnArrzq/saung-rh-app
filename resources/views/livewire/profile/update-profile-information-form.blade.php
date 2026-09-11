<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = (string) ($user->phone ?? '');
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        // Dinormalkan sebelum cek unik — users.email di Postgres peka huruf besar-kecil.
        $this->email = mb_strtolower(trim($this->email));

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi — dipakai untuk masuk.',
            'email.email' => 'Format email belum benar, mis. nama@contoh.com.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'phone.regex' => 'Nomor HP hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'phone.max' => 'Nomor HP maksimal 30 karakter.',
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['phone'] = filled($validated['phone'] ?? null) ? trim((string) $validated['phone']) : null;

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        $user = Auth::user();

        return [
            'roleLabels' => $user->roles->map(fn ($role) => Role::labelFor((string) $role->name))->implode(', '),
            'memberSince' => $user->created_at?->format('d/m/Y'),
        ];
    }
}; ?>

<form wire:submit="updateProfileInformation" class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-input label="Nama lengkap" name="name" wire:model="name" required autocomplete="name" />

        <x-input label="Nomor HP" name="phone" type="tel" inputmode="numeric" wire:model="phone" autocomplete="tel"
            hint="Opsional." />

        <x-input label="Email" name="email" type="email" wire:model="email" required autocomplete="username"
            hint="Dipakai untuk masuk ke aplikasi." field-class="md:col-span-2" />
    </div>

    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
        <x-alert type="warning" title="Email belum diverifikasi">
            <span class="block">Buka tautan verifikasi yang kami kirim ke email Anda.</span>
            @if (session('status') === 'verification-link-sent')
                <span class="mt-1 block font-medium">Tautan verifikasi baru sudah dikirim.</span>
            @else
                <x-button variant="link" size="sm" class="mt-1 px-0" wire:click="sendVerification" loading="sendVerification">
                    Kirim ulang email verifikasi
                </x-button>
            @endif
        </x-alert>
    @endif

    <dl class="grid gap-3 rounded-lg bg-base-200 p-3 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-base-content/60">Peran</dt>
            <dd class="font-medium">{{ $roleLabels !== '' ? $roleLabels : 'Belum ada peran' }}</dd>
        </div>
        <div>
            <dt class="text-base-content/60">Terdaftar sejak</dt>
            <dd class="font-medium tabular-nums">{{ $memberSince ?? '—' }}</dd>
        </div>
    </dl>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <p x-data="{ shown: false, timer: null }"
            x-on:profile-updated.window="shown = true; clearTimeout(timer); timer = setTimeout(() => shown = false, 2500)"
            x-show="shown" x-transition.opacity.duration.200ms style="display: none"
            class="inline-flex items-center gap-1 text-sm text-success" role="status">
            <i class="ri-checkbox-circle-line" aria-hidden="true"></i> Data diri tersimpan.
        </p>

        <x-button type="submit" variant="primary" icon="ri-save-line" loading="updateProfileInformation">
            Simpan data diri
        </x-button>
    </div>
</form>
