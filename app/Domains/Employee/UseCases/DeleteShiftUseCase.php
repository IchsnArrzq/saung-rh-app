<?php

namespace App\Domains\Employee\UseCases;

use App\Domains\Employee\Repositories\ShiftRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteShiftUseCase
{
    public function __construct(private readonly ShiftRepositoryInterface $shifts) {}

    public function handle(string $shiftId): void
    {
        $shift = $this->shifts->find($shiftId);

        if (! $shift) {
            throw ValidationException::withMessages([
                'shift' => 'Shift tidak ditemukan.',
            ]);
        }

        DB::transaction(fn () => $this->shifts->delete($shift));
    }
}
