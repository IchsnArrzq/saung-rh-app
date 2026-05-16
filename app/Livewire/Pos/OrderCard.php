<?php

namespace App\Livewire\Pos;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Support\Collection;
use Livewire\Component;

class OrderCard extends Component
{
    public ?int $activeCategoryId = null;

    public function setCategory(?int $categoryId = null): void
    {
        $this->activeCategoryId = $categoryId;
    }

    public function getCategoriesProperty(): Collection
    {
        return MenuCategory::query()
            ->where('is_active', true)
            ->withCount(['menus' => function ($query) {
                $query->where('is_available', true);
            }])
            ->orderBy('name')
            ->get();
    }

    public function getMenusProperty(): Collection
    {
        return Menu::query()
            ->with('category:id,name')
            ->where('is_available', true)
            ->when($this->activeCategoryId, fn ($query) => $query->where('menu_category_id', $this->activeCategoryId))
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.pos.order-card', [
            'categories' => $this->categories,
            'menus' => $this->menus,
            'totalAvailableMenus' => Menu::query()->where('is_available', true)->count(),
        ]);
    }
}
