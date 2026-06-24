<?php

namespace App\Livewire\Staff\Waiter;

use App\Models\Order;
use App\Models\ServiceLog;
use App\Models\Table;
use App\Models\Tip;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class TipsServiceLog extends Component
{
    // Tip form
    public ?string $tipTableId = null;

    public ?string $tipOrderId = null;

    public ?string $tipAmount = null;

    public ?string $tipNote = null;

    // Service log form
    public ?string $svcTableId = null;

    public string $svcType = 'greeting';

    public ?string $svcDescription = null;

    public function saveTip(): void
    {
        $validated = $this->validate([
            'tipAmount' => ['required', 'numeric', 'min:1'],
            'tipTableId' => ['nullable', 'exists:tables,id'],
            'tipOrderId' => ['nullable', 'exists:orders,id'],
            'tipNote' => ['nullable', 'string', 'max:255'],
        ]);

        Tip::query()->create([
            'waiter_id' => auth()->id(),
            'table_id' => $validated['tipTableId'] ?: null,
            'order_id' => $validated['tipOrderId'] ?: null,
            'amount' => $validated['tipAmount'],
            'note' => $validated['tipNote'] ?: null,
            'received_at' => now(),
        ]);

        $this->reset(['tipTableId', 'tipOrderId', 'tipAmount', 'tipNote']);

        session()->flash('tip_success', 'Tip berhasil dicatat.');
    }

    public function saveService(): void
    {
        $validated = $this->validate([
            'svcType' => ['required', Rule::in(array_keys(ServiceLog::TYPES))],
            'svcTableId' => ['nullable', 'exists:tables,id'],
            'svcDescription' => ['nullable', 'string', 'max:500'],
        ]);

        ServiceLog::query()->create([
            'waiter_id' => auth()->id(),
            'table_id' => $validated['svcTableId'] ?: null,
            'type' => $validated['svcType'],
            'description' => $validated['svcDescription'] ?: null,
            'served_at' => now(),
        ]);

        $this->reset(['svcTableId', 'svcDescription']);
        $this->svcType = 'greeting';

        session()->flash('svc_success', 'Log layanan berhasil dicatat.');
    }

    public function render(): View
    {
        $waiterId = auth()->id();

        $tables = Table::query()->orderBy('code')->get(['id', 'code', 'name']);

        $activeOrders = Order::query()
            ->whereIn('status', ['confirmed', 'preparing', 'ready', 'served'])
            ->orderByDesc('ordered_at')
            ->limit(50)
            ->get(['id', 'order_number', 'customer_name']);

        $todayTips = Tip::query()
            ->where('waiter_id', $waiterId)
            ->whereDate('received_at', today());

        $tipsTotal = (clone $todayTips)->sum('amount');
        $tipsCount = (clone $todayTips)->count();

        $recentTips = Tip::query()
            ->where('waiter_id', $waiterId)
            ->with('table')
            ->latest('received_at')
            ->limit(8)
            ->get();

        $recentServices = ServiceLog::query()
            ->where('waiter_id', $waiterId)
            ->with('table')
            ->latest('served_at')
            ->limit(8)
            ->get();

        return view('livewire.staff.waiter.tips-service-log', [
            'tables' => $tables,
            'activeOrders' => $activeOrders,
            'tipsTotal' => $tipsTotal,
            'tipsCount' => $tipsCount,
            'recentTips' => $recentTips,
            'recentServices' => $recentServices,
            'serviceTypes' => ServiceLog::TYPES,
        ]);
    }
}
