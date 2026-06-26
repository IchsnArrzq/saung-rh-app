<?php

namespace App\Livewire\Customer;

use App\Services\Customer\OrderCartService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['portal' => 'customer'])]
class TablePicker extends Component
{
    public string $search = '';

    public function selectTable(string $tableId, OrderCartService $service): void
    {
        if (! $service->findAvailableTable($tableId)) {
            session()->flash('warning', 'Meja tersebut sudah tidak tersedia.');

            return;
        }

        $service->setActiveTable($tableId);
        $this->redirectRoute('customer.menus.index', ['table_id' => $tableId], navigate: true);
    }

    public function render(OrderCartService $service)
    {
        $activeId = $service->activeTableId();

        return view('livewire.customer.table-picker', [
            ...$service->tableSelectionData($this->search),
            'activeTable' => $activeId ? $service->findOrderableTable($activeId) : null,
        ]);
    }
}
