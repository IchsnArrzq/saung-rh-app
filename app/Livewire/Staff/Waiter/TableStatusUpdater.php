<?php

namespace App\Livewire\Staff\Waiter;

use App\Models\Table;
use App\Models\TableStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class TableStatusUpdater extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updateStatus(string $tableId, string $statusId): void
    {
        $table = Table::query()->findOrFail($tableId);
        $status = TableStatus::query()->findOrFail($statusId);

        if ($table->table_status_id === $status->id) {
            return;
        }

        $table->update(['table_status_id' => $status->id]);

        session()->flash('success', "Meja {$table->code} diubah ke status {$status->name}.");
    }

    public function render(): View
    {
        $search = trim($this->search);

        $statuses = TableStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tables = Table::query()
            ->with(['tableStatus', 'tableCategory'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('code')
            ->get();

        return view('livewire.staff.waiter.table-status-updater', [
            'statuses' => $statuses,
            'tables' => $tables,
        ]);
    }
}
