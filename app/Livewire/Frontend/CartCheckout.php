<?php

namespace App\Livewire\Frontend;

use App\Domains\Order\DTO\PlaceGuestOrderData;
use App\Domains\Order\UseCases\PlaceGuestOrderUseCase;
use App\Domains\Table\Repositories\TableRepository;
use App\Support\RestaurantCart;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Public dine-in checkout. A guest orders for the table bound by their QR
 * check-in (or a table they pick), and the order goes straight to the kitchen.
 * Table reservations are an account feature handled by the customer portal.
 */
#[Layout('layouts.guest')]
class CartCheckout extends Component
{
    public ?string $tableId = null;

    public string $customerName = '';

    public string $notes = '';

    public function mount(): void
    {
        $context = RestaurantCart::context();

        $this->tableId = $context['table_id'];
        $this->customerName = Auth::user()?->name ?? '';
    }

    public function selectTable(string $tableId): void
    {
        $this->tableId = $tableId;
        RestaurantCart::setTableId($tableId);
    }

    public function incrementQty(string $menuId): void
    {
        $cart = RestaurantCart::cart();
        $currentQty = (int) ($cart[$menuId]['qty'] ?? 0);

        if ($currentQty <= 0) {
            return;
        }

        RestaurantCart::setQty($menuId, $currentQty + 1);
    }

    public function decrementQty(string $menuId): void
    {
        $cart = RestaurantCart::cart();
        $currentQty = (int) ($cart[$menuId]['qty'] ?? 0);

        if ($currentQty <= 1) {
            RestaurantCart::removeItem($menuId);

            return;
        }

        RestaurantCart::setQty($menuId, $currentQty - 1);
    }

    public function removeItem(string $menuId): void
    {
        RestaurantCart::removeItem($menuId);
        session()->flash('success', 'Item dihapus dari cart.');
    }

    public function checkout(PlaceGuestOrderUseCase $placeOrder)
    {
        if (RestaurantCart::cart() === []) {
            $this->addError('cart', 'Cart masih kosong.');

            return null;
        }

        $validated = $this->validate([
            'tableId' => ['required', 'exists:tables,id'],
            'customerName' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        $placeOrder->handle(new PlaceGuestOrderData(
            items: RestaurantCart::toOrderItems(),
            tableId: $validated['tableId'],
            customerName: $validated['customerName'] ?? null,
            notes: $validated['notes'] ?? null,
        ));

        RestaurantCart::clearCart();
        RestaurantCart::setTableId($validated['tableId']);

        session()->flash('success', 'Pesanan berhasil dikirim ke dapur.');

        return $this->redirectRoute('public.menu', ['table_id' => $validated['tableId']], navigate: true);
    }

    public function render(TableRepository $tables)
    {
        return view('livewire.frontend.cart-checkout', [
            'cartItems' => collect(RestaurantCart::cart())->values(),
            'subtotal' => RestaurantCart::subtotal(),
            'tables' => $tables->orderable(),
            'tableId' => $this->tableId,
        ]);
    }
}
