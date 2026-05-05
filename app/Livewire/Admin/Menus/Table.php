<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    #[Url(as: 'view', except: 'table')]
    public string $viewMode = 'table';

    public function mount(): void
    {
        $this->authorize('viewAny', Menu::class);

        if (! in_array($this->viewMode, ['table', 'card'], true)) {
            $this->viewMode = 'table';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedViewMode(string $value): void
    {
        if (! in_array($value, ['table', 'card'], true)) {
            $this->viewMode = 'table';
        }
    }

    public function delete(string $id): void
    {
        $menu = Menu::query()->findOrFail($id);
        $this->authorize('delete', $menu);
        $menu->delete();

        session()->flash('success', 'Menu berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $menus = Menu::query()
            ->with('category')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.menus.table', [
            'menus' => $menus,
        ]);
    }
}

