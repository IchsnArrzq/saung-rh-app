<?php

namespace App\Livewire\Admin\Tables;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\QueryUseCases\GetTableListQueryUseCase;
use App\Domains\Table\Repositories\TableRepositoryInterface;
use App\Domains\Table\UseCases\ChangeTableStatusUseCase;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class StatusBoard extends Component
{
    #[Url(as: 'boardSearch', except: '')]
    public string $search = '';

    /**
     * The target now arrives as an Enum value instead of a table_statuses UUID.
     * Every status is always shown — the old `showInactiveStatuses` toggle
     * disappeared with the `is_active` column.
     */
    public function moveTable(string $tableId, string $targetStatus, ChangeTableStatusUseCase $changeStatus, TableRepositoryInterface $tables): void
    {
        $target = TableStatus::tryFrom($targetStatus);
        $table = $tables->find($tableId);

        if (! $target || ! $table) {
            return;
        }

        $changeStatus->handle($table, $target);

        session()->flash('success', "Meja {$table->code} dipindahkan ke status {$target->label()}.");
    }

    public function render(GetTableListQueryUseCase $tableList): View
    {
        $tablesByStatus = $tableList->search($this->search)
            ->groupBy(fn ($table) => (string) $table->status);

        return view('livewire.admin.tables.status-board', [
            'statuses' => collect(TableStatus::cases())
                ->sortBy(fn (TableStatus $status) => $status->sortOrder())
                ->values(),
            'tablesByStatus' => $tablesByStatus,
        ]);
    }
}
