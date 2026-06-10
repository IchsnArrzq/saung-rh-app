<?php

namespace App\Livewire\Admin\Reports;

use App\Exports\SalesReportExport;
use App\Services\Admin\ReportService;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ReportBoard extends Component
{
    public $startDate;
    public $endDate;
    public $filterType = 'today';

    public function mount()
    {
        $this->setFilter('today');
    }

    public function setFilter($type)
    {
        $this->filterType = $type;
        $now = Carbon::now();

        if ($type === 'today') {
            $this->startDate = $now->copy()->format('Y-m-d');
            $this->endDate = $now->copy()->format('Y-m-d');
        } elseif ($type === 'this_month') {
            $this->startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($type === 'this_year') {
            $this->startDate = $now->copy()->startOfYear()->format('Y-m-d');
            $this->endDate = $now->copy()->endOfYear()->format('Y-m-d');
        }
    }

    public function updatedStartDate()
    {
        $this->filterType = 'custom';
    }

    public function updatedEndDate()
    {
        $this->filterType = 'custom';
    }

    public function exportExcel()
    {
        $fileName = 'Laporan_Penjualan_' . $this->startDate . '_sd_' . $this->endDate . '.xlsx';
        return Excel::download(new SalesReportExport($this->startDate, $this->endDate), $fileName);
    }

    public function render(ReportService $reportService)
    {
        $data = $reportService->getReportData($this->startDate, $this->endDate);

        $this->dispatch('chart-updated', data: [
            'labels' => $data['chartLabels'],
            'values' => $data['chartValues']
        ]);

        return view('livewire.admin.reports.report-board', $data);
    }
}
