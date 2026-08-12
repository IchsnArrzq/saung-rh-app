<?php

namespace App\Domains\System\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class UserRepository implements UserRepositoryInterface
{
    public function find(string $id): ?User
    {
        return User::query()->find($id);
    }

    public function activeWithRole(string $role): Collection
    {
        return User::query()
            ->role($role)
            ->where('is_active', true)
            ->get();
    }

    public function namesByIds(iterable $ids): SupportCollection
    {
        return User::query()
            ->whereIn('id', collect($ids)->all())
            ->pluck('name', 'id');
    }
}
