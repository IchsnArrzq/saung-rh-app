<?php

namespace App\Services\Manager;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ShiftService
{
    /**
     * Schedule a shift for a staff member.
     */
    public function schedule(string $userId, string $date, string $startsAt, string $endsAt, ?string $position = null, ?string $notes = null): Shift
    {
        return Shift::query()->create([
            'user_id' => $userId,
            'shift_date' => $date,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'position' => $position ?: null,
            'status' => 'scheduled',
            'notes' => $notes ?: null,
        ]);
    }

    public function setStatus(Shift $shift, string $status): Shift
    {
        if (in_array($status, Shift::STATUSES, true)) {
            $shift->update(['status' => $status]);
        }

        return $shift;
    }

    public function delete(Shift $shift): void
    {
        $shift->delete();
    }

    /**
     * Shifts for a given week (Mon–Sun) grouped by date (Y-m-d).
     *
     * @return \Illuminate\Support\Collection<string, Collection<int, Shift>>
     */
    public function week(Carbon $anchor)
    {
        $start = $anchor->copy()->startOfWeek();
        $end = $anchor->copy()->endOfWeek();

        return Shift::query()
            ->with('user')
            ->whereBetween('shift_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Shift $s): string => $s->shift_date->toDateString());
    }

    /**
     * Staff that can be scheduled (non-customer roles).
     *
     * @return Collection<int, User>
     */
    public function schedulableStaff(): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($q) => $q->whereNotIn('name', ['customer']))
            ->orderBy('name')
            ->get();
    }
}
