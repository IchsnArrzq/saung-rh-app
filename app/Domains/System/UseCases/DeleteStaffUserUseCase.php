<?php

namespace App\Domains\System\UseCases;

use App\Domains\System\Repositories\UserRepository;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Hapus akun karyawan. Superadmin dan akun milik penghapus sendiri tidak pernah
 * dihapus. Akun yang masih dirujuk data lain (pesanan, shift, tip) ditolak
 * database lewat foreign key — ditangkap dan dijawab dengan saran menonaktifkan,
 * bukan halaman 500.
 */
class DeleteStaffUserUseCase
{
    public function __construct(private readonly UserRepository $users) {}

    public function handle(User $user, User $actor): void
    {
        if ($user->hasRole(Role::LOCKED)) {
            throw ValidationException::withMessages(['user' => 'Akun Superadmin tidak bisa dihapus.']);
        }

        if ($user->is($actor)) {
            throw ValidationException::withMessages(['user' => 'Anda tidak bisa menghapus akun Anda sendiri.']);
        }

        try {
            DB::transaction(fn () => $this->users->delete($user));
        } catch (QueryException $e) {
            Log::warning('Akun karyawan tidak bisa dihapus karena masih dirujuk data lain.', [
                'user_id' => $user->id,
                'actor_id' => $actor->id,
                'action' => 'delete_staff_user',
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'user' => 'Akun '.$user->name.' masih tercatat di data lain (pesanan, shift, atau tip), jadi tidak bisa dihapus. '
                    .'Nonaktifkan saja supaya tidak bisa masuk lagi.',
            ]);
        }
    }
}
