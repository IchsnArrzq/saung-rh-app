<?php

namespace App\Domains\System\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface UserRepositoryInterface
{
    public function find(string $id): ?User;

    /**
     * Everyone holding a role who can still log in.
     *
     * @return Collection<int, User>
     */
    public function activeWithRole(string $role): Collection;

    /**
     * id => name, for screens that show people by id (leaderboards, top
     * customers) without loading whole records.
     *
     * @param  iterable<int, string>  $ids
     * @return SupportCollection<string, string>
     */
    public function namesByIds(iterable $ids): SupportCollection;
}
