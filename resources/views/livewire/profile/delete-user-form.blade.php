<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $user = Auth::user();

        // Akun karyawan — apalagi Superadmin — tidak menghapus dirinya sendiri; ia
        // dikelola admin di halaman Karyawan. Menghapusnya dari sini bisa mengunci
        // restoran keluar dari aplikasinya sendiri. Formnya juga tidak ditawarkan
        // (profile.blade.php); pengecekan ini untuk request langsung.
        if ($this->holdsStaffRole($user)) {
            $this->addError('password', 'Akun karyawan tidak bisa dihapus dari sini. Minta admin menonaktifkannya di halaman Karyawan.');

            return;
        }

        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ], [
            'password.required' => 'Masukkan kata sandi untuk memastikan ini memang Anda.',
            'password.current_password' => 'Kata sandi tidak cocok.',
        ]);

        tap($user, $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }

    /** Memegang peran apa pun selain pelanggan = akun karyawan. */
    private function holdsStaffRole(User $user): bool
    {
        return $user->roles->contains(fn ($role) => $role->name !== 'customer');
    }
}; ?>

<section class="space-y-4">
    <p class="text-sm text-base-content/70">
        Akun Anda dan data yang terhubung dengannya dihapus permanen. Tindakan ini tidak bisa dibatalkan.
    </p>

    <x-button variant="error" outline icon="ri-delete-bin-line" x-data
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        Hapus akun saya
    </x-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" maxWidth="md" focusable>
        <form wire:submit="deleteUser" class="space-y-4">
            <div>
                <h3 class="text-lg font-semibold">Hapus akun Anda?</h3>
                <p class="mt-1 text-sm text-base-content/70">
                    Masukkan kata sandi untuk memastikan ini memang Anda. Akun yang sudah dihapus tidak bisa dikembalikan.
                </p>
            </div>

            <x-field label="Kata sandi" name="password" for="delete-account-password" required>
                <x-password-input id="delete-account-password" wire:model="password" autocomplete="current-password" />
            </x-field>

            <div class="flex flex-wrap justify-end gap-2">
                <x-button variant="ghost" x-on:click="$dispatch('close')">Batal</x-button>
                <x-button type="submit" variant="error" icon="ri-delete-bin-line" loading="deleteUser">
                    Hapus akun permanen
                </x-button>
            </div>
        </form>
    </x-modal>
</section>
