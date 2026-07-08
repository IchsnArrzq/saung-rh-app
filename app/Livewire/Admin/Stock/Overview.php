<?php

namespace App\Livewire\Admin\Stock;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Overview extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'low', except: '')]
    public string $lowOnly = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingLowOnly(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $query = Ingredient::query()
            ->where('is_active', true)
            ->when($search !== '', fn (Builder $q) => $q->where('name', 'like', '%'.$search.'%'))
            ->when($this->lowOnly !== '', fn (Builder $q) => $q->whereColumn('stock', '<=', 'min_stock'));

        $ingredients = (clone $query)->orderBy('name')->paginate(20);

        // Summary across all active ingredients (not just the current page).
        $all = Ingredient::query()->where('is_active', true)->get(['stock', 'min_stock', 'cost_per_unit']);
        $totalValue = $all->sum(fn ($i) => (float) $i->stock * (float) ($i->cost_per_unit ?? 0));
        $lowCount = $all->filter(fn ($i) => (float) $i->stock <= (float) $i->min_stock)->count();

        return view('livewire.admin.stock.overview', [
            'ingredients' => $ingredients,
            'totalItems' => $all->count(),
            'lowCount' => $lowCount,
            'totalValue' => $totalValue,
        ]);
    }
}
