<?php

namespace App\Livewire\Staff\Manager;

use App\Domains\Employee\QueryUseCases\GetStaffKpiQueryUseCase;
use App\Domains\Reporting\Enums\AnalyticsRange;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class StaffKpi extends Component
{
    #[Url(as: 'range', except: 'week')]
    public string $range = 'week';

    public function setRange(string $range): void
    {
        // Ignore anything unknown rather than snapping back to the default —
        // the panel keeps whatever window the manager was already looking at.
        $this->range = AnalyticsRange::tryFrom($range)?->value ?? $this->range;
    }

    public function render(GetStaffKpiQueryUseCase $kpi): View
    {
        $staff = $kpi->handle(AnalyticsRange::fromRequest($this->range));

        return view('livewire.staff.manager.staff-kpi', [
            'staff' => $staff,
            'maxScore' => max(0.1, (float) $staff->max('score')),
        ]);
    }
}
