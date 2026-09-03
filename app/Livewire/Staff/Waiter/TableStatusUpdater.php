<?php

namespace App\Livewire\Staff\Waiter;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\QueryUseCases\GetTableListQueryUseCase;
use App\Domains\Table\Repositories\TableRepository;
use App\Domains\Table\UseCases\ChangeTableStatusUseCase;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class TableStatusUpdater extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    /**
     * The status now arrives as an Enum value ("available", "cleaning", …)
     * instead of a table_statuses UUID — the Blade passes `$status->value`.
     */
    public function updateStatus(string $tableId, string $status, ChangeTableStatusUseCase $changeStatus, TableRepository $tables): void
    {
        $target = TableStatus::tryFrom($status);
        $table = $tables->find($tableId);

        if (! $target || ! $table) {
            return;
        }

        $changeStatus->handle($table, $target);

        session()->flash('success', "Meja {$table->code} diubah ke status {$target->label()}.");
    }

    public function render(GetTableListQueryUseCase $tableList): View
    {
        return view('livewire.staff.waiter.table-status-updater', [
            'statuses' => collect(TableStatus::cases())
                ->sortBy(fn (TableStatus $status) => $status->sortOrder())
                ->values(),
            'tables' => $tableList->search($this->search),
        ]);
    }
}
