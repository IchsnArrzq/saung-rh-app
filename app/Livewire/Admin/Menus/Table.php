<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
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

    /**
     * Route-nya sudah dijaga `can:viewAny`, tapi komponen ini juga bisa
     * dipasang di halaman lain — gerbangnya ikut komponen, bukan ikut route.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Menu::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $menu = Menu::query()->findOrFail($id);

        // Wajib meski tombolnya sudah disembunyikan @can di Blade: aksi Livewire
        // adalah endpoint HTTP, dan siapa pun bisa memanggilnya tanpa tombol itu.
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
