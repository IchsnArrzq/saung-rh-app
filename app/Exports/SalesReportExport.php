<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $startDate;
    protected $endDate;

    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
    }

    public function query()
    {
        return Order::query()
            ->with(['table', 'cashier'])
            ->whereBetween('ordered_at', [$this->startDate, $this->endDate])
            ->where('status', 'paid')
            ->orderBy('ordered_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'No Order',
            'Tanggal',
            'Meja',
            'Kasir',
            'Total',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->ordered_at->format('Y-m-d H:i'),
            $order->table->code ?? 'Takeaway',
            $order->cashier->name ?? '-',
            $order->total,
        ];
    }
}
