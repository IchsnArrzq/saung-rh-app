<?php

namespace App\Livewire\Staff\Receptionist;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class TopAnalytics extends Component
{
    #[Url(as: 'range', except: 'week')]
    public string $range = 'week';

    public function setRange(string $range): void
    {
        if (in_array($range, ['today', 'week', 'month'], true)) {
            $this->range = $range;
        }
    }

    private function rangeStart(): CarbonImmutable
    {
        return match ($this->range) {
            'today' => CarbonImmutable::now()->startOfDay(),
            'month' => CarbonImmutable::now()->startOfMonth(),
            default => CarbonImmutable::now()->startOfWeek(),
        };
    }

    public function render(): View
    {
        $start = $this->rangeStart();
        $countedStatuses = ['served', 'paid'];

        $topMenus = OrderItem::query()
            ->select(
                'menu_name_snapshot',
                DB::raw('sum(qty) as total_qty'),
                DB::raw('sum(line_total) as total_revenue'),
            )
            ->whereHas('order', fn ($q) => $q->whereIn('status', $countedStatuses)
                ->where('ordered_at', '>=', $start))
            ->groupBy('menu_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $maxQty = max(1, (int) $topMenus->max('total_qty'));

        $ordersInRange = Order::query()
            ->whereIn('status', $countedStatuses)
            ->where('ordered_at', '>=', $start);

        return view('livewire.staff.receptionist.top-analytics', [
            'topMenus' => $topMenus,
            'maxQty' => $maxQty,
            'totalOrders' => (clone $ordersInRange)->count(),
            'totalRevenue' => (float) (clone $ordersInRange)->sum('total'),
            'totalItems' => (int) $topMenus->sum('total_qty'),
        ]);
    }
}
