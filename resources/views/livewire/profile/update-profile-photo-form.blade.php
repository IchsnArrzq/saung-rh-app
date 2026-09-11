<?php

use App\Domains\System\UseCases\RemoveProfilePhotoUseCase;
use App\Domains\System\UseCases\UpdateProfilePhotoUseCase;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $photo = null;

    /**
     * Foto langsung disimpan begitu selesai terunggah — satu klik lebih sedikit,
     * dan pratinjaunya adalah foto yang benar-benar tersimpan.
     */
    public function updatedPhoto(): void
    {
        $this->validate([
            'photo' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo.image' => 'Berkasnya harus berupa gambar.',
            'photo.mimes' => 'Gunakan foto berformat JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        app(UpdateProfilePhotoUseCase::class)->handle(Auth::user(), $this->photo);

        $this->reset('photo');
        $this->dispatch('profile-photo-updated');
        session()->flash('photo_status', 'Foto profil diperbarui.');
    }

    public function removePhoto(RemoveProfilePhotoUseCase $removePhoto): void
    {
        $removePhoto->handle(Auth::user());

        $this->dispatch('profile-photo-updated');
        session()->flash('photo_status', 'Foto profil dihapus. Inisial nama Anda tampil sebagai gantinya.');
    }
}; ?>

@php
    $user = auth()->user();
    $preview = $photo && method_exists($photo, 'isPreviewable') && $photo->isPreviewable() && ! $errors->has('photo')
        ? $photo->temporaryUrl()
        : $user->avatar_url;
@endphp

<section class="space-y-4">
    @if (session('photo_status'))
        <x-alert type="success">{{ session('photo_status') }}</x-alert>
    @endif

    <x-image-upload name="photo" wire:model="photo" label="Foto profil" shape="circle" :preview="$preview"
        hint="PNG, JPG, atau WEBP — maks 2 MB. Foto langsung tersimpan setelah selesai terunggah." />

    @if ($user->avatar_path)
        <x-button variant="error" outline size="sm" icon="ri-delete-bin-line" wire:click="removePhoto" loading="removePhoto"
            data-confirm="Hapus foto profil? Inisial nama Anda akan tampil sebagai gantinya.">
            Hapus foto
        </x-button>
    @endif
</section>
