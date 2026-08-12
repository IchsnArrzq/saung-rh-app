<?php

namespace App\Domains\Employee\Repositories;

use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface ShiftRepositoryInterface
{
    public function find(string $id): ?Shift;

    /**
     * One week of the roster (Mon–Sun around `$anchor`), grouped by date key
     * `Y-m-d` — the shape the scheduler grid renders from.
     *
     * @return SupportCollection<string, Collection<int, Shift>>
     */
    public function week(CarbonInterface $anchor): SupportCollection;

    /**
     * Everyone who may be put on the roster — every role except customers.
     *
     * Lives here rather than in a User repository because "who is rosterable"
     * is a rostering rule, not a fact about the user record.
     *
     * @return Collection<int, User>
     */
    public function schedulableStaff(): Collection;

    /**
     * Which of the given users are rostered (still `scheduled`) on a date.
     *
     * @param  array<int, string>  $userIds
     * @return array<int, string>
     */
    public function onShiftUserIdsForDate(array $userIds, CarbonInterface $date): array;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Shift;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Shift $shift, array $attributes): Shift;

    public function delete(Shift $shift): void;
}
