<?php

namespace App\Domains\System\UseCases;

use App\Domains\System\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Ganti foto profil. Berkas baru disimpan dengan nama baru (bukan menimpa), jadi
 * URL-nya berubah dan browser tidak menampilkan foto lama dari cache; berkas lama
 * dihapus setelah baris user menunjuk ke yang baru.
 */
class UpdateProfilePhotoUseCase
{
    public function __construct(private readonly UserRepository $users) {}

    public function handle(User $user, UploadedFile $photo): User
    {
        $previous = $user->avatar_path;

        $path = $photo->store('avatars/'.$user->id, 'public');

        $user = $this->users->update($user, ['avatar_path' => $path]);

        if ($previous && $previous !== $path) {
            Storage::disk('public')->delete($previous);
        }

        return $user;
    }
}
