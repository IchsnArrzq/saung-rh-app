<?php

namespace App\Livewire\Staff\Manager;

use App\Services\Manager\ManagerAnalyticsService;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class TopCustomers extends Component
{
    #[Url(as: 'range', except: 'month')]
    public string $range = 'month';

    public function setRange(string $range): void
    {
        if (in_array($range, ['today', 'week', 'month'], true)) {
            $this->range = $range;
        }
    }

    public function render(ManagerAnalyticsService $analytics): View
    {
        $customers = $analytics->topCustomers($this->range);

        return view('livewire.staff.manager.top-customers', [
            'customers' => $customers,
            'maxSpend' => max(1.0, (float) $customers->max('total_spend')),
        ]);
    }
}
