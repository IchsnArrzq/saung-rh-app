<?php

namespace App\Livewire\Admin\Ingredients;

use App\Models\Ingredient;
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
        $this->authorize('viewAny', Ingredient::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $ingredient = Ingredient::query()->findOrFail($id);

        $this->authorize('delete', $ingredient);

        $ingredient->delete();

        session()->flash('success', 'Bahan berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $ingredients = Ingredient::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->whereLike('name', '%'.$search.'%')
                        ->orWhereLike('unit', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.ingredients.table', [
            'ingredients' => $ingredients,
        ]);
    }
}
