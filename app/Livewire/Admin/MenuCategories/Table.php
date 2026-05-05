<?php

namespace App\Livewire\Admin\MenuCategories;

use App\Models\MenuCategory;
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

    public function mount(): void
    {
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $menuCategory = MenuCategory::query()->findOrFail($id);
        $menuCategory->delete();

        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $categories = MenuCategory::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.menu-categories.table', [
            'categories' => $categories,
        ]);
    }
}

