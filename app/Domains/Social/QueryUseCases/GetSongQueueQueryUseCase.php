<?php

namespace App\Domains\Social\QueryUseCases;

use App\Domains\Social\Repositories\SongRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * The two lists the DJ board draws, plus what a single table sees of its own
 * requests under the submit form.
 */
class GetSongQueueQueryUseCase
{
    public function __construct(private readonly SongRequestRepositoryInterface $songs) {}

    /** Playing first, then oldest queued. */
    public function queue(): Collection
    {
        return $this->songs->queue();
    }

    public function recentlyFinished(int $limit = 8): Collection
    {
        return $this->songs->recentlyFinished($limit);
    }

    public function forSession(?string $sessionId, int $limit = 10): Collection
    {
        return $sessionId ? $this->songs->forSession($sessionId, $limit) : new Collection;
    }

    public function activeCountForSession(?string $sessionId): int
    {
        return $sessionId ? $this->songs->activeCountForSession($sessionId) : 0;
    }
}
