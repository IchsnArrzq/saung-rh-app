<?php

namespace App\Livewire\Pos;

use App\Domains\Menu\Enums\MenuAvailability;
use App\Domains\Menu\QueryUseCases\GetMenuCatalogQueryUseCase;
use App\Domains\Menu\Repositories\MenuRepository;
use App\Domains\Order\DTO\PlacePosOrderData;
use App\Domains\Order\UseCases\PlacePosOrderUseCase;
use App\Domains\Payment\Enums\PaymentMethod;
use App\Domains\Table\Repositories\TableRepository;
use App\Support\RestaurantCart;
use Illuminate\Support\Collection;
use Livewire\Component;

class OrderCard extends Component
{
    public ?int $activeCategoryId = null;
    public string $search = '';
    public ?string $tableId = null;
    public string $customerName = '';
    public string $notes = '';
    public bool $payNow = true;
    public string $paymentMethod = 'cash';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedMenu = null;

    public function mount(): void
    {
        $this->customerName = (string) (auth()->user()?->name ?? '');
    }

    public function setCategory(?int $categoryId = null): void
    {
        $this->activeCategoryId = $categoryId;
    }

    public function addToCart(string $menuId, MenuRepository $menus): void
    {
        $menu = $menus->find($menuId);

        if (! $menu || ! $menu->is_available) {
            $this->addError('cart', 'Menu tidak tersedia.');

            return;
        }

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

    public function showMenuDetail(string $menuId, MenuRepository $menus): void
    {
        $menu = $menus->find($menuId);

        if (! $menu) {
            return;
        }

        $menu->loadMissing('category:id,name');
        $availability = MenuAvailability::tryFrom((string) $menu->status);

        $this->selectedMenu = [
            'id' => (string) $menu->id,
            'name' => (string) $menu->name,
            'price' => (float) $menu->price,
            'description' => (string) ($menu->description ?? ''),
            'image_url' => (string) ($menu->image_url ?? ''),
            'is_available' => (bool) $menu->is_available,
            'category_name' => (string) ($menu->category?->name ?? 'Uncategorized'),
            'status_name' => $availability?->label()
                ?? ($menu->is_available ? 'Tersedia' : 'Tidak Tersedia'),
            'status_color' => $availability?->color()
                ?? ($menu->is_available ? 'success' : 'error'),
            'sku' => (string) ($menu->sku ?? '-'),
        ];

        $this->dispatch('open-modal', 'menu-detail-modal');
    }

    public function closeMenuDetail(): void
    {
        $this->dispatch('close-modal', 'menu-detail-modal');
        $this->selectedMenu = null;
    }

    public function placeOrder(PlacePosOrderUseCase $placeOrder): void
    {
        $this->tableId = is_string($this->tableId) && trim($this->tableId) === '' ? null : $this->tableId;

        $validated = $this->validate([
            'tableId' => ['nullable', 'exists:tables,id'],
            'customerName' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'payNow' => ['boolean'],
            'paymentMethod' => ['required_if:payNow,true', 'in:'.implode(',', PaymentMethod::values())],
        ]);

        if (RestaurantCart::cart() === []) {
            $this->addError('cart', 'Order masih kosong.');

            return;
        }

        $customerName = trim((string) ($validated['customerName'] ?? ''));

        $order = $placeOrder->handle(new PlacePosOrderData(
            items: RestaurantCart::toOrderItems(),
            tableId: trim((string) ($validated['tableId'] ?? '')) ?: null,
            customerName: $customerName !== '' ? $customerName : null,
            notes: $validated['notes'] ?? null,
            payNow: (bool) ($validated['payNow'] ?? false),
            paymentMethod: PaymentMethod::tryFrom((string) ($validated['paymentMethod'] ?? '')),
        ));

        RestaurantCart::clearCart();
        $this->notes = '';
        $this->customerName = (string) (auth()->user()?->name ?? '');
        $this->tableId = null;
        $this->payNow = true;
        $this->paymentMethod = PaymentMethod::Cash->value;
        $this->resetValidation();

        session()->flash('success', 'Order '.$order->order_number.' berhasil disimpan.');
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

    public function render(GetMenuCatalogQueryUseCase $catalog, TableRepository $tables)
    {
        return view('livewire.pos.order-card', [
            'categories' => $catalog->categories(),
            'menus' => $catalog->availableFiltered($this->search, $this->activeCategoryId),
            'totalAvailableMenus' => $catalog->countAvailable(),
            'cartItems' => $this->cartItems,
            'cartCount' => $this->cartCount,
            'cartSubtotal' => $this->cartSubtotal,
            'tables' => $tables->allOrdered(),
        ]);
    }
}
