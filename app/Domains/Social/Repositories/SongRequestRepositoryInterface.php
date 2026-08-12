<?php

namespace App\Domains\Social\Repositories;

use App\Models\SongRequest;
use Illuminate\Database\Eloquent\Collection;

interface SongRequestRepositoryInterface
{
    public function find(string $id): ?SongRequest;

    /** How many slots of a table's queue cap are already taken. */
    public function activeCountForSession(string $sessionId): int;

    /** The live queue: playing first, then oldest request. */
    public function queue(): Collection;

    /**
     * One table's own history, newest first — what the guest sees under the
     * request form.
     *
     * @return Collection<int, SongRequest>
     */
    public function forSession(string $sessionId, int $limit = 10): Collection;

    /**
     * Recently played or rejected, for the DJ board's bottom strip.
     *
     * @return Collection<int, SongRequest>
     */
    public function recentlyFinished(int $limit = 8): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SongRequest;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SongRequest $song, array $attributes): SongRequest;
}
