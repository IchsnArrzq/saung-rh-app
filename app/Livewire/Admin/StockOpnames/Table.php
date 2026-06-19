<?php

namespace App\Livewire\Admin\StockOpnames;

use App\Models\Ingredient;
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

    #[Url(as: 'type', except: '')]
    public string $typeFilter = '';

    #[Url(as: 'ingredient', except: '')]
    public string $ingredientFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingIngredientFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $records = StockOpname::query()
            ->with(['ingredient', 'user'])
            ->when($this->typeFilter !== '', fn (Builder $q) => $q->where('type', $this->typeFilter))
            ->when($this->ingredientFilter !== '', fn (Builder $q) => $q->where('ingredient_id', $this->ingredientFilter))
            ->when($search !== '', function (Builder $q) use ($search): void {
                $q->where(function (Builder $inner) use ($search): void {
                    $inner->where('notes', 'like', '%'.$search.'%')
                        ->orWhereHas('ingredient', fn (Builder $i) => $i->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.stock-opnames.table', [
            'records' => $records,
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
        ]);
    }
}
