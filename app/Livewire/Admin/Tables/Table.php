<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Table as DiningTable;
use App\Models\TableStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use AuthorizesRequests;
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

    public function updateStatus(string $tableId): void
    {
        $table = DiningTable::query()->findOrFail($tableId);
        $this->authorize('update', $table);

        $statusId = $this->statusDrafts[$tableId] ?? null;

        if (! $statusId) {
            throw ValidationException::withMessages([
                'table_status_id' => 'Status meja harus dipilih.',
            ]);
        }

        $status = TableStatus::query()->find($statusId);

        if (! $status) {
            throw ValidationException::withMessages([
                'table_status_id' => 'Status meja tidak valid.',
            ]);
        }

        $table->update([
            'table_status_id' => $status->id,
            'status' => $status->key,
        ]);

        session()->flash('success', 'Status meja berhasil diperbarui.');
    }

    public function delete(string $id): void
    {
        $table = DiningTable::query()->findOrFail($id);
        $this->authorize('delete', $table);
        $table->delete();

        session()->flash('success', 'Meja berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $tables = DiningTable::query()
            ->with(['tableStatus', 'tableCategory'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('capacity', 'like', '%'.$search.'%')
                        ->orWhereHas('tableStatus', fn (Builder $status) => $status->where(function (Builder $statusQuery) use ($search): void {
                            $statusQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('key', 'like', '%'.$search.'%');
                        }))
                        ->orWhereHas('tableCategory', fn (Builder $category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('code')
            ->paginate(12);

        $statusOptions = TableStatus::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($tables as $table) {
            if (! isset($this->statusDrafts[$table->id])) {
                $this->statusDrafts[$table->id] = (string) ($table->table_status_id ?? '');
            }
        }

        return view('livewire.admin.tables.table', [
            'tables' => $tables,
            'statusOptions' => $statusOptions,
        ]);
    }
}

