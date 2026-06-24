<?php

namespace App\Livewire\Frontend;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Table;
use App\Support\RestaurantCart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class MenuCatalog extends Component
{
    public string $mode = RestaurantCart::MODE_ONLINE;

    public ?string $tableId = null;

    public ?int $activeCategoryId = null;

    public string $search = '';

    /** @var array<string, mixed>|null */
    public ?array $selectedMenu = null;

    public function mount(): void
    {
        $context = RestaurantCart::syncContextFromRequest(request());

        $this->mode = $context['mode'];
        $this->tableId = $context['table_id'];
    }

    public function setMode(string $mode): void
    {
        $context = RestaurantCart::setMode($mode);

        $this->mode = $context['mode'];
        $this->tableId = $context['table_id'];
    }

    public function setCategory(?int $categoryId = null): void
    {
        $this->activeCategoryId = $categoryId;
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
            'sku' => (string) ($menu->sku ?? '-'),
        ];

        $this->dispatch('open-modal', 'menu-detail-modal');
    }

    public function closeMenuDetail(): void
    {
        $this->dispatch('close-modal', 'menu-detail-modal');
        $this->selectedMenu = null;
    }

    public function quickAdd(string $menuId): void
    {
        $menu = Menu::query()->available()->find($menuId);

        if (! $menu) {
            $this->addError('cart', 'Menu tidak ditemukan atau sedang tidak tersedia.');

            return;
        }

        RestaurantCart::addItem($menu, 1);
    }

    public function incrementQty(string $menuId): void
    {
        $cart = RestaurantCart::cart();

        if (! isset($cart[$menuId])) {
            return;
        }

        RestaurantCart::setQty($menuId, ((int) $cart[$menuId]['qty']) + 1);
    }

    public function decrementQty(string $menuId): void
    {
        $cart = RestaurantCart::cart();

        if (! isset($cart[$menuId])) {
            return;
        }

        $qty = (int) $cart[$menuId]['qty'];

        if ($qty <= 1) {
            RestaurantCart::removeItem($menuId);

            return;
        }

        RestaurantCart::setQty($menuId, $qty - 1);
    }

    public function removeItem(string $menuId): void
    {
        RestaurantCart::removeItem($menuId);
    }

    public function clearCart(): void
    {
        RestaurantCart::clearCart();
    }

    public function goToCart()
    {
        return $this->redirectRoute('public.cart.index', navigate: true);
    }

    public function getCategoriesProperty(): Collection
    {
        return MenuCategory::query()
            ->where('is_active', true)
            ->withCount(['menus' => fn ($q) => $q->available()])
            ->orderBy('name')
            ->get();
    }

    public function getMenusProperty(): Collection
    {
        $search = trim($this->search);

        return Menu::query()
            ->with('category:id,name')
            ->available()
            ->when($this->activeCategoryId, fn ($q) => $q->where('menu_category_id', $this->activeCategoryId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function getTotalAvailableProperty(): int
    {
        return Menu::query()->available()->count();
    }

    public function render()
    {
        $selectedTable = $this->tableId
            ? Table::query()->find($this->tableId)
            : null;

        return view('livewire.frontend.menu-catalog', [
            'categories' => $this->categories,
            'menus' => $this->menus,
            'totalAvailable' => $this->totalAvailable,
            'selectedTable' => $selectedTable,
            'cartCount' => RestaurantCart::count(),
            'cartItems' => collect(RestaurantCart::cart())->values(),
            'cartSubtotal' => RestaurantCart::subtotal(),
            'mode' => $this->mode,
            'tableId' => $this->tableId,
        ]);
    }
}
