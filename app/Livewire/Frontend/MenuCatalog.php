<?php

namespace App\Livewire\Frontend;

use App\Models\Menu;
use App\Models\Table;
use App\Support\RestaurantCart;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class MenuCatalog extends Component
{
    public string $mode = RestaurantCart::MODE_ONLINE;

    public ?string $tableId = null;

    public ?string $detailMenuId = null;

    public int $detailQty = 1;

    public string $detailNotes = '';

    public string $search = '';

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

    public function showDetail(string $menuId): void
    {
        $this->detailMenuId = $menuId;
        $this->detailQty = 1;
        $this->detailNotes = '';
    }

    public function closeDetail(): void
    {
        $this->detailMenuId = null;
        $this->detailQty = 1;
        $this->detailNotes = '';
    }

    public function quickAdd(string $menuId): void
    {
        $menu = Menu::query()->where('is_available', true)->find($menuId);

        if (! $menu) {
            $message = 'Menu tidak ditemukan atau sedang tidak tersedia.';

            $this->addError('cart', $message);
            $this->dispatchCartNotification('error', 'Gagal Menambahkan', $message);

            return;
        }

        RestaurantCart::addItem($menu, 1);

        $message = $menu->name.' ditambahkan ke cart.';

        session()->flash('success', $message);
        $this->dispatchCartNotification('success', 'Berhasil', $message);
    }

    public function addDetailToCart(): void
    {
        $validator = Validator::make([
            'detailMenuId' => $this->detailMenuId,
            'detailQty' => $this->detailQty,
            'detailNotes' => $this->detailNotes,
        ], [
            'detailMenuId' => ['required', 'exists:menus,id'],
            'detailQty' => ['required', 'integer', 'min:1', 'max:20'],
            'detailNotes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first() ?: 'Data cart tidak valid.';

            $this->setErrorBag($validator->errors());
            $this->dispatchCartNotification('error', 'Gagal Menambahkan', $message);

            return;
        }

        $menu = Menu::query()->where('is_available', true)->find($this->detailMenuId);

        if (! $menu) {
            $message = 'Menu tidak ditemukan atau sedang tidak tersedia.';

            $this->addError('cart', $message);
            $this->dispatchCartNotification('error', 'Gagal Menambahkan', $message);

            return;
        }

        RestaurantCart::addItem($menu, $this->detailQty, trim($this->detailNotes) ?: null);

        $message = $menu->name.' ditambahkan ke cart.';

        session()->flash('success', $message);
        $this->dispatchCartNotification('success', 'Berhasil', $message);

        $this->closeDetail();
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

    public function render()
    {
        $menus = Menu::query()
            ->with('category')
            ->where('is_available', true)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->get();

        $detailMenu = $this->detailMenuId
            ? Menu::query()->with('category')->find($this->detailMenuId)
            : null;

        $selectedTable = $this->tableId
            ? Table::query()->find($this->tableId)
            : null;

        return view('livewire.frontend.menu-catalog', [
            'menus' => $menus,
            'detailMenu' => $detailMenu,
            'selectedTable' => $selectedTable,
            'cartCount' => RestaurantCart::count(),
            'cartItems' => collect(RestaurantCart::cart())->values(),
            'cartSubtotal' => RestaurantCart::subtotal(),
        ]);
    }

    private function dispatchCartNotification(string $type, string $title, string $message): void
    {
        $this->dispatch('cart-notification', type: $type, title: $title, message: $message);
    }
}
