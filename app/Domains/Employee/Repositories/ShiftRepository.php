<?php

namespace App\Domains\Employee\Repositories;

use App\Domains\Employee\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ShiftRepository
{
    public function find(string $id): ?Shift
    {
        return Shift::query()->find($id);
    }

    /**
     * One week of the roster (Mon–Sun around `$anchor`), grouped by date key
     * `Y-m-d` — the shape the scheduler grid renders from.
     *
     * @return SupportCollection<string, Collection<int, Shift>>
     */
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

    /**
     * Everyone who may be put on the roster — every role except customers.
     *
     * Lives here rather than in a User repository because "who is rosterable"
     * is a rostering rule, not a fact about the user record.
     *
     * @return Collection<int, User>
     */
    public function schedulableStaff(): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereNotIn('name', ['customer']))
            ->orderBy('name')
            ->get();
    }

    /**
     * Which of the given users are rostered (still `scheduled`) on a date.
     *
     * @param  array<int, string>  $userIds
     * @return array<int, string>
     */
    public function onShiftUserIdsForDate(array $userIds, CarbonInterface $date): array
    {
        return Shift::query()
            ->forDate($date)
            ->where('status', ShiftStatus::Scheduled->value)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Shift
    {
        return Shift::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
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
