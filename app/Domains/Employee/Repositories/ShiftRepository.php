<?php

namespace App\Domains\Employee\Repositories;

use App\Domains\Employee\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ShiftRepository implements ShiftRepositoryInterface
{
    public function find(string $id): ?Shift
    {
        return Shift::query()->find($id);
    }

    public function week(CarbonInterface $anchor): SupportCollection
    {
        $start = $anchor->copy()->startOfWeek();
        $end = $anchor->copy()->endOfWeek();

        return Shift::query()
            ->with('user')
            ->whereBetween('shift_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Shift $shift): string => $shift->shift_date->toDateString());
    }

    public function schedulableStaff(): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereNotIn('name', ['customer']))
            ->orderBy('name')
            ->get();
    }

    public function onShiftUserIdsForDate(array $userIds, CarbonInterface $date): array
    {
        return Shift::query()
            ->forDate($date)
            ->where('status', ShiftStatus::Scheduled->value)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->all();
    }

    public function create(array $attributes): Shift
    {
        return Shift::query()->create($attributes);
    }

    public function update(Shift $shift, array $attributes): Shift
    {
        $shift->update($attributes);

        return $shift;
    }

    public function delete(Shift $shift): void
    {
        $shift->delete();
    }
}
