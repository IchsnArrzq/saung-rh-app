<?php

namespace App\Livewire\Admin\Tables;

use App\Domains\Table\Enums\TableStatus;
use App\Domains\Table\QueryUseCases\FindTableQueryUseCase;
use App\Domains\Table\QueryUseCases\GetTableListQueryUseCase;
use App\Domains\Table\UseCases\ChangeTableStatusUseCase;
use App\Models\Table as DiningTable;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    /**
     * @var array<string, string>
     */
    public array $statusDrafts = [];

    public function mount(): void
    {
        $this->authorize('viewAny', DiningTable::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updateStatus(string $tableId, ChangeTableStatusUseCase $changeStatus, FindTableQueryUseCase $findTable): void
    {
        $table = $findTable->byId($tableId);

        if (! $table) {
            return;
        }

        $this->authorize('update', $table);

        $status = TableStatus::tryFrom((string) ($this->statusDrafts[$tableId] ?? ''));

        if (! $status) {
            throw ValidationException::withMessages([
                'status' => 'Status meja tidak valid.',
            ]);
        }

        $changeStatus->handle($table, $status);

        session()->flash('success', 'Status meja berhasil diperbarui.');
    }

    public function delete(string $id, FindTableQueryUseCase $findTable): void
    {
        $table = $findTable->byId($id);

        if (! $table) {
            return;
        }

        $this->authorize('delete', $table);

        $table->delete();

        session()->flash('success', 'Meja berhasil dihapus.');
    }

    public function render(GetTableListQueryUseCase $tableList): View
    {
        $search = trim($this->search);

        $tables = $tableList->paginate($search);

        $statusOptions = collect(TableStatus::cases())
            ->sortBy(fn (TableStatus $status) => $status->sortOrder())
            ->values();

        foreach ($tables as $table) {
            if (! isset($this->statusDrafts[$table->id])) {
                $this->statusDrafts[$table->id] = (string) $table->status;
            }
        }

        return view('livewire.admin.tables.table', [
            'tables' => $tables,
            'statusOptions' => $statusOptions,
        ]);
    }
}
