<?php

namespace App\Services\Manager;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface ShiftServiceInterface
{
    /**
     * Schedule a shift for a staff member.
     */
    public function schedule(string $userId, string $date, string $startsAt, string $endsAt, ?string $position = null, ?string $notes = null): Shift;

    public function setStatus(Shift $shift, string $status): Shift;

    public function delete(Shift $shift): void;

    /**
     * Shifts for a given week (Mon–Sun) grouped by date (Y-m-d).
     *
     * @return \Illuminate\Support\Collection<string, Collection<int, Shift>>
     */
    public function week(Carbon $anchor);

    /**
     * Staff that can be scheduled (non-customer roles).
     *
     * @return Collection<int, User>
     */
    public function schedulableStaff(): Collection;
}
