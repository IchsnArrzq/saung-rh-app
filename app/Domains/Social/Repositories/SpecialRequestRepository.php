<?php

namespace App\Domains\Social\Repositories;

use App\Domains\Social\Enums\SpecialRequestStatus;
use App\Models\SpecialRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class SpecialRequestRepository implements SpecialRequestRepositoryInterface
{
    public function find(string $id): ?SpecialRequest
    {
        return SpecialRequest::query()->find($id);
    }

    public function findAssignedTo(string $id, string $waiterId): ?SpecialRequest
    {
        return SpecialRequest::query()
            ->where('assigned_to', $waiterId)
            ->find($id);
    }

    public function pending(): Collection
    {
        return SpecialRequest::query()
            ->where('status', SpecialRequestStatus::Pending->value)
            ->latest()
            ->get();
    }

    public function recentlyHandled(int $limit = 10): Collection
    {
        return SpecialRequest::query()
            ->whereIn('status', SpecialRequestStatus::handledValues())
            ->with('assignee')
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    public function openFor(string $waiterId): Collection
    {
        return SpecialRequest::query()
            ->where('assigned_to', $waiterId)
            ->where('status', SpecialRequestStatus::Assigned->value)
            ->latest()
            ->get();
    }

    public function countDoneTodayFor(string $waiterId): int
    {
        return SpecialRequest::query()
            ->where('assigned_to', $waiterId)
            ->where('status', SpecialRequestStatus::Done->value)
            ->whereDate('handled_at', today())
            ->count();
    }

    public function forSession(string $sessionId, int $limit = 8): Collection
    {
        return SpecialRequest::query()
            ->where('table_session_id', $sessionId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function activeLoadByAssignee(array $waiterIds): SupportCollection
    {
        return SpecialRequest::query()
            ->where('status', SpecialRequestStatus::Assigned->value)
            ->whereIn('assigned_to', $waiterIds)
            ->selectRaw('assigned_to, count(*) as c')
            ->groupBy('assigned_to')
            ->pluck('c', 'assigned_to')
            ->map(fn ($count) => (int) $count);
    }

    public function create(array $attributes): SpecialRequest
    {
        return SpecialRequest::query()->create($attributes);
    }

    public function update(SpecialRequest $request, array $attributes): SpecialRequest
    {
        $request->update($attributes);

        return $request;
    }
}
