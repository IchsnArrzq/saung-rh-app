<?php

namespace App\Domains\Social\Repositories;

use App\Models\SpecialRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface SpecialRequestRepositoryInterface
{
    public function find(string $id): ?SpecialRequest;

    /** One request, but only if it belongs to this waiter. */
    public function findAssignedTo(string $id, string $waiterId): ?SpecialRequest;

    /**
     * @return Collection<int, SpecialRequest>
     */
    public function pending(): Collection;

    /**
     * Everything past the manager's desk, newest activity first.
     *
     * @return Collection<int, SpecialRequest>
     */
    public function recentlyHandled(int $limit = 10): Collection;

    /**
     * Still on one waiter's plate.
     *
     * @return Collection<int, SpecialRequest>
     */
    public function openFor(string $waiterId): Collection;

    public function countDoneTodayFor(string $waiterId): int;

    /**
     * One table's own requests, newest first.
     *
     * @return Collection<int, SpecialRequest>
     */
    public function forSession(string $sessionId, int $limit = 8): Collection;

    /**
     * How many open requests each of the given waiters is already carrying:
     * user_id => count. Missing keys mean zero.
     *
     * @param  array<int, string>  $waiterIds
     * @return SupportCollection<string, int>
     */
    public function activeLoadByAssignee(array $waiterIds): SupportCollection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SpecialRequest;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SpecialRequest $request, array $attributes): SpecialRequest;
}
