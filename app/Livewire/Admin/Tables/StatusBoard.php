<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Table as DiningTable;
use App\Models\TableStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class StatusBoard extends Component
{
    #[Url(as: 'boardSearch', except: '')]
    public string $search = '';

    public bool $showInactiveStatuses = false;

    public function moveTable(string $tableId, string $targetStatusId): void
    {
        $table = DiningTable::query()->findOrFail($tableId);
        $targetStatus = TableStatus::query()->findOrFail($targetStatusId);

        if ($table->table_status_id === $targetStatus->id) {
            return;
        }

        $table->update([
            'table_status_id' => $targetStatus->id,
        ]);

        session()->flash('success', "Meja {$table->code} dipindahkan ke status {$targetStatus->name}.");
    }

    public function render(): View
    {
        $search = trim($this->search);

        $statuses = TableStatus::query()
            ->when(! $this->showInactiveStatuses, fn (Builder $query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with([
                'tables' => function ($query) use ($search): void {
                    $query->with(['tableCategory', 'tableStatus'])
                        ->when($search !== '', function ($tableQuery) use ($search): void {
                            $tableQuery->where(function (Builder $inner) use ($search): void {
                                $inner->where('code', 'like', '%'.$search.'%')
                                    ->orWhere('name', 'like', '%'.$search.'%')
                                    ->orWhere('capacity', 'like', '%'.$search.'%')
                                    ->orWhereHas('tableCategory', fn (Builder $category) => $category->where('name', 'like', '%'.$search.'%'));
                            });
                        })
                        ->orderBy('code');
                },
            ])
            ->get();

        return view('livewire.admin.tables.status-board', [
            'statuses' => $statuses,
        ]);
    }
}
