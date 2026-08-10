<?php

namespace App\Domains\Social\Repositories;

use App\Domains\Social\Enums\SongStatus;
use App\Models\SongRequest;
use Illuminate\Database\Eloquent\Collection;

class SongRequestRepository implements SongRequestRepositoryInterface
{
    public function find(string $id): ?SongRequest
    {
        return SongRequest::query()->find($id);
    }

    public function activeCountForSession(string $sessionId): int
    {
        return SongRequest::query()
            ->where('table_session_id', $sessionId)
            ->whereIn('status', SongStatus::activeValues())
            ->count();
    }

    public function queue(): Collection
    {
        return SongRequest::query()
            ->whereIn('status', SongStatus::activeValues())
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [SongStatus::Playing->value])
            ->orderBy('created_at')
            ->get();
    }

    public function forSession(string $sessionId, int $limit = 10): Collection
    {
        return SongRequest::query()
            ->where('table_session_id', $sessionId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function recentlyFinished(int $limit = 8): Collection
    {
        return SongRequest::query()
            ->whereIn('status', SongStatus::finishedValues())
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    public function create(array $attributes): SongRequest
    {
        return SongRequest::query()->create($attributes);
    }

    public function update(SongRequest $song, array $attributes): SongRequest
    {
        $song->update($attributes);

        return $song;
    }
}
