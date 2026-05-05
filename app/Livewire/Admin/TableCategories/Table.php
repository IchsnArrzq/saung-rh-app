<?php

namespace App\Livewire\Admin\TableCategories;

use App\Models\TableCategory;
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

    public function mount(): void
    {
        $this->authorize('viewAny', TableCategory::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $tableCategory = TableCategory::query()->findOrFail($id);
        $this->authorize('delete', $tableCategory);

        if ($tableCategory->tables()->exists()) {
            throw ValidationException::withMessages([
                'table_category' => 'Kategori tidak bisa dihapus karena masih dipakai pada data meja.',
            ]);
        }

        $tableCategory->delete();

        session()->flash('success', 'Kategori meja berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $tableCategories = TableCategory::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.table-categories.table', [
            'tableCategories' => $tableCategories,
        ]);
    }
}

