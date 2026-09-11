<?php

namespace App\Domains\Social\Repositories;

use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Models\SpecialRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;

class SpecialRequestRepository
{
    public function find(string $id): ?SpecialRequest
    {
        return Str::isUuid($id) ? SpecialRequest::query()->find($id) : null;
    }

    /**
     * One table's own requests, newest first — the guest's panel.
     *
     * @return Collection<int, SpecialRequest>
     */
    public function forSession(string $sessionId, int $limit = 8): Collection
    {
        return SpecialRequest::query()
            ->with('category')
            ->where('table_session_id', $sessionId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Open requests at a table's current (active) session, oldest first — whoever
     * has waited longest is served first. A request left over from an earlier
     * session is not "happening at this table now", so it is not shown.
     *
     * @return Collection<int, SpecialRequest>
     */
    public function openForTable(string $tableId): Collection
    {
        return $this->atActiveSession($tableId)
            ->with(['category', 'assignee:id,name'])
            ->whereIn('status', SpecialRequestStatus::openValues())
            ->oldest()
            ->get();
    }

    /**
     * The last few handled at the same session, for context under the open list.
     *
     * @return Collection<int, SpecialRequest>
     */
    public function recentlyClosedForTable(string $tableId, int $limit = 5): Collection
    {
        return $this->atActiveSession($tableId)
            ->with(['category', 'assignee:id,name'])
            ->whereIn('status', SpecialRequestStatus::closedValues())
            ->latest('handled_at')
            ->limit($limit)
            ->get();
    }

    /**
     * table_id => open request count, active sessions only. Missing keys mean zero.
     *
     * @return SupportCollection<string, int>
     */
    public function openCountsByTable(): SupportCollection
    {
        return SpecialRequest::query()
            ->whereIn('status', SpecialRequestStatus::openValues())
            ->whereNotNull('table_id')
            ->whereHas('tableSession', fn (Builder $session) => $session->active())
            ->selectRaw('table_id, count(*) as c')
            ->groupBy('table_id')
            ->pluck('c', 'table_id')
            ->map(fn ($count) => (int) $count);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SpecialRequest
    {
        return SpecialRequest::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SpecialRequest $request, array $attributes): SpecialRequest
    {
        $request->update($attributes);

        return $request;
    }

    /**
     * @return Builder<SpecialRequest>
     */
    private function atActiveSession(string $tableId): Builder
    {
        return SpecialRequest::query()
            ->where('table_id', $tableId)
            ->whereHas('tableSession', fn (Builder $session) => $session->active());
    }
}
