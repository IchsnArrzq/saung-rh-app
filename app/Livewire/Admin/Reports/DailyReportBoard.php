<?php

namespace App\Livewire\Admin\Reports;

use App\Services\Admin\ReportService;
use Livewire\Component;

class DailyReportBoard extends Component
{
    public function render(ReportService $reportService)
    {
        $data = $reportService->getDailyReportData();

        return view('livewire.admin.reports.daily-report-board', [
            'totalSales' => $data['totalSales'],
            'totalCustomers' => $data['totalCustomers'],
            'bestSellingMenus' => $data['bestSellingMenus'],
            'revenuePerCashier' => $data['revenuePerCashier'],
        ]);
    }
}
