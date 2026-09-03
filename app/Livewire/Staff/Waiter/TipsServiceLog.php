<?php

namespace App\Livewire\Staff\Waiter;

use App\Domains\Employee\Enums\ServiceType;
use App\Domains\Employee\QueryUseCases\GetWaiterActivityQueryUseCase;
use App\Domains\Employee\UseCases\LogServiceUseCase;
use App\Domains\Employee\UseCases\LogTipUseCase;
use App\Domains\Order\Repositories\OrderRepository;
use App\Domains\Table\Repositories\TableRepository;
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

    public function saveTip(LogTipUseCase $logTip): void
    {
        $validated = $this->validate([
            'tipAmount' => ['required', 'numeric', 'min:1'],
            'tipTableId' => ['nullable', 'exists:tables,id'],
            'tipOrderId' => ['nullable', 'exists:orders,id'],
            'tipNote' => ['nullable', 'string', 'max:255'],
        ]);

        $logTip->handle(
            amount: (float) $validated['tipAmount'],
            tableId: $validated['tipTableId'] ?? null,
            orderId: $validated['tipOrderId'] ?? null,
            note: $validated['tipNote'] ?? null,
        );

        $this->reset(['tipTableId', 'tipOrderId', 'tipAmount', 'tipNote']);

        session()->flash('tip_success', 'Tip berhasil dicatat.');
    }

    public function saveService(LogServiceUseCase $logService): void
    {
        $validated = $this->validate([
            'svcType' => ['required', Rule::in(ServiceType::values())],
            'svcTableId' => ['nullable', 'exists:tables,id'],
            'svcDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $logService->handle(
            type: ServiceType::from($validated['svcType']),
            tableId: $validated['svcTableId'] ?? null,
            description: $validated['svcDescription'] ?? null,
        );

        $this->reset(['svcTableId', 'svcDescription']);
        $this->svcType = ServiceType::default()->value;

        session()->flash('svc_success', 'Log layanan berhasil dicatat.');
    }

    public function render(
        GetWaiterActivityQueryUseCase $activity,
        TableRepository $tables,
        OrderRepository $orders,
    ): View {
        return view('livewire.staff.waiter.tips-service-log', [
            ...$activity->handle(),
            'tables' => $tables->allOrdered(),
            'activeOrders' => $orders->inServiceRecent(),
            'serviceTypes' => ServiceType::options(),
        ]);
    }
}
