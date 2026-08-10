<?php

namespace App\Livewire\Staff\Manager;

use App\Domains\Employee\DTO\ShiftData;
use App\Domains\Employee\QueryUseCases\GetWeekScheduleQueryUseCase;
use App\Domains\Employee\UseCases\DeleteShiftUseCase;
use App\Domains\Employee\UseCases\ScheduleShiftUseCase;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class ShiftScheduler extends Component
{
    public string $weekAnchor = '';

    // Form fields
    public string $userId = '';

    public string $shiftDate = '';

    public string $startsAt = '09:00';

    public string $endsAt = '17:00';

    public string $position = '';

    public function mount(): void
    {
        $this->weekAnchor = today()->toDateString();
        $this->shiftDate = today()->toDateString();
    }

    public function previousWeek(): void
    {
        $this->weekAnchor = Carbon::parse($this->weekAnchor)->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->weekAnchor = Carbon::parse($this->weekAnchor)->addWeek()->toDateString();
    }

    public function save(ScheduleShiftUseCase $scheduleShift): void
    {
        $validated = $this->validate([
            'userId' => ['required', 'exists:users,id'],
            'shiftDate' => ['required', 'date'],
            'startsAt' => ['required', 'date_format:H:i'],
            'endsAt' => ['required', 'date_format:H:i', 'after:startsAt'],
            'position' => ['nullable', 'string', 'max:60'],
        ]);

        $scheduleShift->handle(ShiftData::fromValidated($validated));

        $this->reset(['userId', 'position']);
        session()->flash('shift_status', 'Shift berhasil dijadwalkan.');
    }

    public function deleteShift(DeleteShiftUseCase $deleteShift, string $id): void
    {
        $deleteShift->handle($id);
        session()->flash('shift_status', 'Shift dihapus.');
    }

    public function render(GetWeekScheduleQueryUseCase $schedule): View
    {
        $anchor = Carbon::parse($this->weekAnchor);

        return view('livewire.staff.manager.shift-scheduler', [
            'staff' => $schedule->schedulableStaff(),
            'days' => $schedule->days($anchor),
            'shiftsByDay' => $schedule->shiftsByDay($anchor),
        ]);
    }
}
