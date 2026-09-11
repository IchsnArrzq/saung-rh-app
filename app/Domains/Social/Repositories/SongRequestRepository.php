<?php

namespace App\Domains\Social\Repositories;

use App\Domains\Social\Enums\SongStatus;
use App\Models\SongRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;

class SongRequestRepository
{
    public function find(string $id): ?SongRequest
    {
        return Str::isUuid($id) ? SongRequest::query()->find($id) : null;
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
     * table_id => queued/playing songs at the table's current session. Missing
     * keys mean zero.
     *
     * @return SupportCollection<string, int>
     */
    public function activeCountsByTable(): SupportCollection
    {
        return SongRequest::query()
            ->whereIn('status', SongStatus::activeValues())
            ->whereNotNull('table_id')
            ->whereHas('tableSession', fn (Builder $session) => $session->active())
            ->selectRaw('table_id, count(*) as c')
            ->groupBy('table_id')
            ->pluck('c', 'table_id')
            ->map(fn ($count) => (int) $count);
    }

    /**
     * A table's queued/playing songs at its current session: playing first,
     * then oldest queued — the staff Panel meja.
     *
     * @return Collection<int, SongRequest>
     */
    public function activeForTable(string $tableId): Collection
    {
        return $this->atActiveSession($tableId)
            ->whereIn('status', SongStatus::activeValues())
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [SongStatus::Playing->value])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return Collection<int, SongRequest>
     */
    public function recentlyFinishedForTable(string $tableId, int $limit = 5): Collection
    {
        return $this->atActiveSession($tableId)
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

    /**
     * @return Builder<SongRequest>
     */
    private function atActiveSession(string $tableId): Builder
    {
        return SongRequest::query()
            ->where('table_id', $tableId)
            ->whereHas('tableSession', fn (Builder $session) => $session->active());
    }
}
