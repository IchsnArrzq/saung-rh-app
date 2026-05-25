<?php

namespace App\Livewire\Pos;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Support\RestaurantCart;
use Illuminate\Support\Collection;
use Livewire\Component;

class OrderCard extends Component
{
    public ?int $activeCategoryId = null;
    public string $search = '';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedMenu = null;

    public function setCategory(?int $categoryId = null): void
    {
        $this->activeCategoryId = $categoryId;
    }

    public function addToCart(string $menuId): void
    {
        $menu = Menu::query()
            ->where('is_available', true)
            ->findOrFail($menuId);

        RestaurantCart::addItem($menu, 1);
    }

    public function incrementQty(string $menuId): void
    {
        $item = RestaurantCart::cart()[$menuId] ?? null;
        if (! $item) {
            return;
        }

        RestaurantCart::setQty($menuId, ((int) $item['qty']) + 1);
    }

    public function decrementQty(string $menuId): void
    {
        $item = RestaurantCart::cart()[$menuId] ?? null;
        if (! $item) {
            return;
        }

        $qty = (int) $item['qty'];
        if ($qty <= 1) {
            RestaurantCart::removeItem($menuId);

            return;
        }

        RestaurantCart::setQty($menuId, $qty - 1);
    }

    public function removeCartItem(string $menuId): void
    {
        RestaurantCart::removeItem($menuId);
    }

    public function clearCart(): void
    {
        RestaurantCart::clearCart();
    }

    public function showMenuDetail(string $menuId): void
    {
        $menu = Menu::query()
            ->with(['category:id,name', 'status:id,name,key,color'])
            ->findOrFail($menuId);

        $this->selectedMenu = [
            'id' => (string) $menu->id,
            'name' => (string) $menu->name,
            'price' => (float) $menu->price,
            'description' => (string) ($menu->description ?? ''),
            'image_url' => (string) ($menu->image_url ?? ''),
            'is_available' => (bool) $menu->is_available,
            'category_name' => (string) ($menu->category?->name ?? 'Uncategorized'),
            'status_name' => (string) ($menu->status?->name ?? ($menu->is_available ? 'Tersedia' : 'Tidak Tersedia')),
            'status_color' => (string) ($menu->status?->color ?? ($menu->is_available ? 'success' : 'error')),
            'sku' => (string) ($menu->sku ?? '-'),
        ];

        $this->dispatch('open-modal', 'menu-detail-modal');
    }

    public function closeMenuDetail(): void
    {
        $this->dispatch('close-modal', 'menu-detail-modal');
        $this->selectedMenu = null;
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
        $search = trim($this->search);

        return Menu::query()
            ->with('category:id,name')
            ->where('is_available', true)
            ->when($this->activeCategoryId, fn ($query) => $query->where('menu_category_id', $this->activeCategoryId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'ilike', '%'.$search.'%')
                        ->orWhere('description', 'ilike', '%'.$search.'%')
                        ->orWhere('sku', 'ilike', '%'.$search.'%')
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'ilike', '%'.$search.'%'));
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function getCartItemsProperty(): Collection
    {
        return collect(RestaurantCart::cart())->values();
    }

    public function getCartCountProperty(): int
    {
        return RestaurantCart::count();
    }

    public function getCartSubtotalProperty(): float
    {
        return RestaurantCart::subtotal();
    }

    public function render()
    {
        return view('livewire.pos.order-card', [
            'categories' => $this->categories,
            'menus' => $this->menus,
            'totalAvailableMenus' => Menu::query()->where('is_available', true)->count(),
            'cartItems' => $this->cartItems,
            'cartCount' => $this->cartCount,
            'cartSubtotal' => $this->cartSubtotal,
        ]);
    }
}
