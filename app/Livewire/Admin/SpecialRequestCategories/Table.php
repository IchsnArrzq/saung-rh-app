<?php

namespace App\Livewire\Admin\SpecialRequestCategories;

use App\Domains\Social\QueryUseCases\GetSpecialRequestCategoriesQueryUseCase;
use App\Domains\Social\UseCases\DeleteSpecialRequestCategoryUseCase;
use App\Models\SpecialRequestCategory;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', SpecialRequestCategory::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id, GetSpecialRequestCategoriesQueryUseCase $categories, DeleteSpecialRequestCategoryUseCase $deleteCategory): void
    {
        $category = $categories->find($id);

        if (! $category) {
            session()->flash('error', 'Kategori tidak ditemukan. Mungkin sudah dihapus — muat ulang halaman.');

            return;
        }

        $this->authorize('delete', $category);

        try {
            $deleteCategory->handle($category);
        } catch (ValidationException $e) {
            session()->flash('error', (string) collect($e->errors())->flatten()->first());

            return;
        }

        session()->flash('success', 'Kategori "'.$category->name.'" dihapus.');
    }

    public function render(GetSpecialRequestCategoriesQueryUseCase $categories): View
    {
        return view('livewire.admin.special-request-categories.table', [
            'categories' => $categories->paginate($this->search),
        ]);
    }
}
