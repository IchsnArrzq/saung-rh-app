<?php

namespace App\Livewire\Customer;

use App\Domains\Customer\Services\CustomerCart;
use App\Domains\Table\QueryUseCases\FindTableQueryUseCase;
use App\Domains\Table\QueryUseCases\GetTableListQueryUseCase;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['portal' => 'customer'])]
class TablePicker extends Component
{
    public string $search = '';

    public function selectTable(string $tableId, FindTableQueryUseCase $findTable, CustomerCart $cart): void
    {
        if (! $findTable->free($tableId)) {
            session()->flash('warning', 'Meja tersebut sudah tidak tersedia.');

            return;
        }

        $cart->setActiveTable($tableId);
        $this->redirectRoute('customer.menus.index', ['table_id' => $tableId], navigate: true);
    }

    public function render(GetTableListQueryUseCase $tableList, FindTableQueryUseCase $findTable, CustomerCart $cart)
    {
        $activeId = $cart->activeTableId();

        return view('livewire.customer.table-picker', [
            ...$tableList->groupedByStatus($this->search),
            // Still orderable, not merely still existing — a party that has
            // already ordered keeps its banner even though the table is busy.
            'activeTable' => $activeId ? $findTable->orderable($activeId) : null,
        ]);
    }
}
