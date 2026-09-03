<?php

namespace App\Domains\System\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class UserRepository
{
    public function find(string $id): ?User
    {
        return User::query()->find($id);
    }

    /**
     * Everyone holding a role who can still log in.
     *
     * @return Collection<int, User>
     */
    public function activeWithRole(string $role): Collection
    {
        return User::query()
            ->role($role)
            ->where('is_active', true)
            ->get();
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
}
