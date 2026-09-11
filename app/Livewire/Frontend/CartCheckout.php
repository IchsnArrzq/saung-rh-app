<?php

namespace App\Livewire\Frontend;

use App\Domains\Order\DTO\PlaceGuestOrderData;
use App\Domains\Order\UseCases\PlaceGuestOrderUseCase;
use App\Domains\Table\QueryUseCases\GetTableSessionQueryUseCase;
use App\Support\RestaurantCart;
use App\Support\TableSessionContext;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Public dine-in checkout. The order goes to the table of this phone's QR
 * session — there is no table picker, and the session must have been approved
 * by staff (PlaceGuestOrderUseCase re-checks that on every send). Table
 * reservations are an account feature handled by the customer portal.
 */
#[Layout('layouts.guest')]
class CartCheckout extends Component
{
    public string $customerName = '';

    public string $notes = '';

    public function mount(GetTableSessionQueryUseCase $sessions): void
    {
        $this->customerName = $sessions->live(TableSessionContext::sessionId())?->customer_name
            ?? Auth::user()?->name
            ?? '';
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

        $sessionId = TableSessionContext::sessionId();

        if (! $sessionId) {
            $this->addError('cart', 'Pesan di tempat butuh sesi meja. Scan QR yang ada di meja Anda dulu.');

            return null;
        }

        $validated = $this->validate([
            'customerName' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        // Refuses (on `cart`) unless the session is approved and still open.
        $placeOrder->handle(new PlaceGuestOrderData(
            items: RestaurantCart::toOrderItems(),
            tableSessionId: $sessionId,
            customerName: $validated['customerName'] ?? null,
            notes: $validated['notes'] ?? null,
        ));

        RestaurantCart::clearCart();

        session()->flash('success', 'Pesanan berhasil dikirim ke dapur.');

        return $this->redirectRoute('public.menu', navigate: true);
    }

    public function render(GetTableSessionQueryUseCase $sessions)
    {
        return view('livewire.frontend.cart-checkout', [
            'cartItems' => collect(RestaurantCart::cart())->values(),
            'subtotal' => RestaurantCart::subtotal(),
            'tableSession' => $sessions->live(TableSessionContext::sessionId()),
        ]);
    }
}
