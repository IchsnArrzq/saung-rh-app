<?php

namespace App\Domains\Social\Repositories;

use App\Domains\Social\Enums\SongStatus;
use App\Models\SongRequest;
use Illuminate\Database\Eloquent\Collection;

class SongRequestRepository
{
    public function find(string $id): ?SongRequest
    {
        return SongRequest::query()->find($id);
    }

    /** How many slots of a table's queue cap are already taken. */
    public function activeCountForSession(string $sessionId): int
    {
        return SongRequest::query()
            ->where('table_session_id', $sessionId)
            ->whereIn('status', SongStatus::activeValues())
            ->count();
    }

    /** The live queue: playing first, then oldest request. */
    public function queue(): Collection
    {
        return SongRequest::query()
            ->whereIn('status', SongStatus::activeValues())
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [SongStatus::Playing->value])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * One table's own history, newest first — what the guest sees under the
     * request form.
     *
     * @return Collection<int, SongRequest>
     */
    public function forSession(string $sessionId, int $limit = 10): Collection
    {
        return SongRequest::query()
            ->where('table_session_id', $sessionId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Recently played or rejected, for the DJ board's bottom strip.
     *
     * @return Collection<int, SongRequest>
     */
    public function recentlyFinished(int $limit = 8): Collection
    {
        return SongRequest::query()
            ->whereIn('status', SongStatus::finishedValues())
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SongRequest
    {
        return SongRequest::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SongRequest $song, array $attributes): SongRequest
    {
        $song->update($attributes);

        return $song;
    }
}
