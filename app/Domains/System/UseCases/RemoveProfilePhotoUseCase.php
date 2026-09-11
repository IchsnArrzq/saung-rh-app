<?php

namespace App\Domains\System\UseCases;

use App\Domains\System\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/** Hapus foto profil — inisial nama tampil sebagai gantinya. */
class RemoveProfilePhotoUseCase
{
    public function __construct(private readonly UserRepository $users) {}

    public function handle(User $user): User
    {
        $previous = $user->avatar_path;

        $user = $this->users->update($user, ['avatar_path' => null]);

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        return $user;
    }
}
