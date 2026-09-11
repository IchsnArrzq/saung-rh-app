<?php

namespace App\Livewire\Admin\StockOpnames;

use App\Models\StockOpname;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    public function mount(): void
    {
        $this->authorize('viewAny', StockOpname::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $opname = StockOpname::query()->findOrFail($id);

        $this->authorize('delete', $opname);

        if ($opname->isPosted()) {
            session()->flash('error', 'Opname yang sudah diposting tidak bisa dihapus.');

            return;
        }

        $opname->delete();

        session()->flash('success', 'Draft opname berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $opnames = StockOpname::query()
            ->withCount('items')
            ->with('user')
            ->when($this->statusFilter !== '', fn (Builder $q) => $q->where('status', $this->statusFilter))
            ->when($search !== '', function (Builder $q) use ($search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->whereLike('code', '%'.$search.'%')
                        ->orWhereLike('notes', '%'.$search.'%');
                });
            })
            ->latest('opname_date')
            ->latest()
            ->paginate(15);

        return view('livewire.admin.stock-opnames.table', [
            'opnames' => $opnames,
        ]);
    }
}
