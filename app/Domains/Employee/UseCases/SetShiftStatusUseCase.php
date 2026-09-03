<?php

namespace App\Domains\Employee\UseCases;

use App\Domains\Employee\Enums\ShiftStatus;
use App\Domains\Employee\Repositories\ShiftRepository;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Marks a rostered shift completed or absent.
 *
 * Deliberately no transition guard: a manager corrects the roster by hand and
 * may flip a shift back to "scheduled" after a mistaken absence — same reasoning
 * as TableStatus. The Enum parameter is the guard that matters, since it makes
 * an invalid value unrepresentable (the old service silently ignored one).
 */
class SetShiftStatusUseCase
{
    public function __construct(private readonly ShiftRepository $shifts) {}

    public function handle(string $shiftId, ShiftStatus $status): Shift
    {
        $shift = $this->shifts->find($shiftId);

        if (! $shift) {
            throw ValidationException::withMessages([
                'shift' => 'Shift tidak ditemukan.',
            ]);
        }

        return DB::transaction(fn (): Shift => $this->shifts->update($shift, ['status' => $status->value]));
    }
}
