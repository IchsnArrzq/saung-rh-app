<?php

namespace App\Livewire\Admin\Reports;

use App\Services\Admin\ReportService;
use Livewire\Component;

class MonthlyReportBoard extends Component
{
    public $month;
    public $year;

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function render(ReportService $reportService)
    {
        $data = $reportService->getMonthlyReportData($this->month, $this->year);

        return view('livewire.admin.reports.monthly-report-board', [
            'totalSales' => $data['totalSales'],
            'totalCustomers' => $data['totalCustomers'],
            'bestSellingMenus' => $data['bestSellingMenus'],
            'revenuePerCashier' => $data['revenuePerCashier'],
        ]);
    }
}
