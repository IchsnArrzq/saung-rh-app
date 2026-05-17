<?php

namespace App\Livewire\Admin\MenuStatuses;

use App\Models\MenuStatus;
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
    private const RESERVED_KEYS = ['available', 'unavailable', 'sold_out', 'seasonal'];

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
        $menuStatus = MenuStatus::query()->findOrFail($id);

        if (in_array($menuStatus->key, self::RESERVED_KEYS, true)) {
            throw ValidationException::withMessages([
                'menu_status' => 'Status sistem tidak dapat dihapus.',
            ]);
        }

        if ($menuStatus->menus()->exists()) {
            throw ValidationException::withMessages([
                'menu_status' => 'Status tidak bisa dihapus karena masih dipakai pada data menu.',
            ]);
        }

        $wasDefault = (bool) $menuStatus->is_default;
        $menuStatus->delete();

        if ($wasDefault) {
            $nextDefault = MenuStatus::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->first()
                ?? MenuStatus::query()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->first();

            if ($nextDefault) {
                $nextDefault->update(['is_default' => true]);
            }
        }

        session()->flash('success', 'Status menu berhasil dihapus.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $menuStatuses = MenuStatus::query()
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

        return view('livewire.admin.menu-statuses.table', [
            'menuStatuses' => $menuStatuses,
            'reservedKeys' => self::RESERVED_KEYS,
        ]);
    }
}
