<?php

namespace App\Domains\Employee\UseCases;

use App\Domains\Employee\DTO\ShiftData;
use App\Domains\Employee\Enums\ShiftStatus;
use App\Domains\Employee\Repositories\ShiftRepositoryInterface;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class ScheduleShiftUseCase
{
    public function __construct(private readonly ShiftRepositoryInterface $shifts) {}

    public function handle(ShiftData $data): Shift
    {
        return DB::transaction(fn (): Shift => $this->shifts->create([
            'user_id' => $data->userId,
            'shift_date' => $data->date,
            'starts_at' => $data->startsAt,
            'ends_at' => $data->endsAt,
            'position' => $data->position,
            'status' => ShiftStatus::default()->value,
            'notes' => $data->notes,
        ]));
    }
}
