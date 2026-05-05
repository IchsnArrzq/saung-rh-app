<?php

namespace App\Livewire\Admin\TableStatuses;

use App\Models\TableStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    /**
     * @var array<int, string>
     */
    private const RESERVED_KEYS = ['available', 'occupied', 'order_in', 'cleaning'];

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public function mount(): void
    {
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $tableStatus = TableStatus::query()->findOrFail($id);

        if (in_array($tableStatus->key, self::RESERVED_KEYS, true)) {
            throw ValidationException::withMessages([
                'table_status' => 'Status sistem tidak dapat dihapus.',
            ]);
        }

        if ($tableStatus->tables()->exists()) {
            throw ValidationException::withMessages([
                'table_status' => 'Status tidak bisa dihapus karena masih dipakai pada data meja.',
            ]);
        }

        $wasDefault = (bool) $tableStatus->is_default;
        $tableStatus->delete();

        if ($wasDefault) {
            $nextDefault = TableStatus::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->first()
                ?? TableStatus::query()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->first();

            if ($nextDefault) {
                $nextDefault->update(['is_default' => true]);
            }
        }

        session()->flash('success', 'Status meja berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $tableStatuses = TableStatus::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('key', 'like', '%'.$search.'%')
                        ->orWhere('color', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.table-statuses.table', [
            'tableStatuses' => $tableStatuses,
            'reservedKeys' => self::RESERVED_KEYS,
        ]);
    }
}

