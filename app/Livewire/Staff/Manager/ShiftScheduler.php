<?php

namespace App\Livewire\Staff\Manager;

use App\Models\Shift;
use App\Services\Manager\ShiftService;
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

    public function save(ShiftService $shifts): void
    {
        $this->validate([
            'userId' => ['required', 'exists:users,id'],
            'shiftDate' => ['required', 'date'],
            'startsAt' => ['required', 'date_format:H:i'],
            'endsAt' => ['required', 'date_format:H:i', 'after:startsAt'],
            'position' => ['nullable', 'string', 'max:60'],
        ]);

        $shifts->schedule($this->userId, $this->shiftDate, $this->startsAt, $this->endsAt, $this->position);

        $this->reset(['userId', 'position']);
        session()->flash('shift_status', 'Shift berhasil dijadwalkan.');
    }

    public function deleteShift(ShiftService $shifts, string $id): void
    {
        $shifts->delete(Shift::query()->findOrFail($id));
        session()->flash('shift_status', 'Shift dihapus.');
    }

    public function render(ShiftService $shifts): View
    {
        $anchor = Carbon::parse($this->weekAnchor);

        return view('livewire.staff.manager.shift-scheduler', [
            'staff' => $shifts->schedulableStaff(),
            'days' => collect(range(0, 6))->map(fn (int $i): Carbon => $anchor->copy()->startOfWeek()->addDays($i)),
            'shiftsByDay' => $shifts->week($anchor),
        ]);
    }
}
