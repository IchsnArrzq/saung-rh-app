<?php

namespace App\Domains\System\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as SupportCollection;

class UserRepository
{
    public function find(string $id): ?User
    {
        return User::query()->find($id);
    }

    /**
     * id => name, for screens that show people by id (leaderboards, top
     * customers) without loading whole records.
     *
     * @param  iterable<int, string>  $ids
     * @return SupportCollection<string, string>
     */
    public function namesByIds(iterable $ids): SupportCollection
    {
        return User::query()
            ->whereIn('id', collect($ids)->all())
            ->pluck('name', 'id');
    }

    /**
     * Akun karyawan: siapa pun yang memegang peran selain pelanggan — termasuk
     * peran buatan layar Peran & hak akses — atau belum punya peran sama sekali
     * (supaya akun seperti itu tetap terlihat dan bisa dibereskan). Pencarian
     * tidak membedakan huruf besar-kecil: whereLike → ILIKE di Postgres.
     */
    public function paginateStaff(string $search = '', string $role = '', int $perPage = 15): LengthAwarePaginator
    {
        $search = trim($search);

        return User::query()
            ->with('roles:id,name')
            ->where(function (Builder $query): void {
                $query->whereHas('roles', fn (Builder $roles) => $roles->where('name', '!=', 'customer'))
                    ->orWhereDoesntHave('roles');
            })
            ->when($role !== '', fn (Builder $query) => $query->whereHas('roles', fn (Builder $roles) => $roles->where('name', $role)))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('email', '%'.$search.'%')
                        ->orWhereLike('phone', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user;
    }

    /** Satu akun karyawan memegang tepat satu peran. */
    public function syncRole(User $user, string $role): void
    {
        $user->syncRoles([$role]);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
