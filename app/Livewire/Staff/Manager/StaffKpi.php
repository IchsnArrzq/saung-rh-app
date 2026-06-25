<?php

namespace App\Livewire\Staff\Manager;

use App\Services\Manager\ManagerAnalyticsService;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class StaffKpi extends Component
{
    #[Url(as: 'range', except: 'week')]
    public string $range = 'week';

    public function setRange(string $range): void
    {
        if (in_array($range, ['today', 'week', 'month'], true)) {
            $this->range = $range;
        }
    }

    public function render(ManagerAnalyticsService $analytics): View
    {
        $staff = $analytics->topStaff($this->range);

        return view('livewire.staff.manager.staff-kpi', [
            'staff' => $staff,
            'maxScore' => max(0.1, (float) $staff->max('score')),
        ]);
    }
}
