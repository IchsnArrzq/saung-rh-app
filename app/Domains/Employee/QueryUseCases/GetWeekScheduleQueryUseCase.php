<?php

namespace App\Domains\Employee\QueryUseCases;

use App\Domains\Employee\Repositories\ShiftRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Everything the weekly roster grid draws: the seven day columns, the shifts
 * inside them, and the staff list the "add shift" form picks from.
 */
class GetWeekScheduleQueryUseCase
{
    public function __construct(private readonly ShiftRepositoryInterface $shifts) {}

    /**
     * @return SupportCollection<string, Collection<int, \App\Models\Shift>>
     */
    public function shiftsByDay(CarbonInterface $anchor): SupportCollection
    {
        return $this->shifts->week($anchor);
    }

    /**
     * The Mon–Sun columns around `$anchor`.
     *
     * @return SupportCollection<int, CarbonInterface>
     */
    public function days(CarbonInterface $anchor): SupportCollection
    {
        $monday = $anchor->copy()->startOfWeek();

        return collect(range(0, 6))->map(fn (int $offset): CarbonInterface => $monday->copy()->addDays($offset));
    }

    /**
     * @return Collection<int, \App\Models\User>
     */
    public function schedulableStaff(): Collection
    {
        return $this->shifts->schedulableStaff();
    }
}
