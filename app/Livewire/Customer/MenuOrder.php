<?php

namespace App\Livewire\Customer;

use App\Domains\Customer\Services\CustomerCart;
use App\Domains\Menu\QueryUseCases\GetMenuCatalogQueryUseCase;
use App\Domains\Order\DTO\PlaceCustomerOrderData;
use App\Domains\Order\UseCases\PlaceCustomerOrderUseCase;
use App\Domains\Table\QueryUseCases\FindTableQueryUseCase;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['portal' => 'customer'])]
class MenuOrder extends Component
{
    use WithPagination;

    public string $tableId = '';

    public string $search = '';

    public ?int $activeCategoryId = null;

    /** @var array<string,mixed>|null */
    public ?array $selectedMenu = null;

    public int $detailQty = 1;

    public string $detailNotes = '';

    public string $orderNotes = '';

    public function mount(FindTableQueryUseCase $findTable, CustomerCart $cart): void
    {
        $tableId = (string) request()->query('table_id', '');

        // Fall back to the seated table so "Pesan Menu" keeps working after the
        // first order moves the table to "order_in".
        if ($tableId === '') {
            $tableId = (string) $cart->activeTableId();
        }

        if ($tableId === '' || ! $findTable->orderable($tableId)) {
            session()->flash('warning', 'Pilih meja terlebih dahulu untuk mulai memesan.');
            $this->redirectRoute('customer.menus.tables', navigate: true);

            return;
        }

        $this->tableId = $tableId;
        $cart->setActiveTable($tableId);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setCategory(?int $categoryId = null): void
    {
        $this->activeCategoryId = $categoryId;
        $this->resetPage();
    }

    public function showMenuDetail(string $menuId, GetMenuCatalogQueryUseCase $catalog): void
    {
        $menu = $catalog->find($menuId);

        if (! $menu) {
            return;
        }

        $this->selectedMenu = [
            'id' => (string) $menu->id,
            'name' => (string) $menu->name,
            'price' => (float) $menu->price,
            'description' => (string) ($menu->description ?? ''),
            'image_url' => (string) ($menu->image_url ?? ''),
            'category_name' => (string) ($menu->category?->name ?? 'Uncategorized'),
        ];
        $this->detailQty = 1;
        $this->detailNotes = '';

        $this->dispatch('open-modal', 'menu-detail-modal');
    }

    public function closeMenuDetail(): void
    {
        $this->dispatch('close-modal', 'menu-detail-modal');
        $this->selectedMenu = null;
    }

    public function addFromDetail(GetMenuCatalogQueryUseCase $catalog, CustomerCart $cart): void
    {
        if (! $this->selectedMenu) {
            return;
        }

        $this->putInCart($catalog, $cart, $this->selectedMenu['id'], max(1, $this->detailQty), $this->detailNotes);

        $this->closeMenuDetail();
        session()->flash('success', 'Menu ditambahkan ke cart.');
    }

    public function quickAdd(string $menuId, GetMenuCatalogQueryUseCase $catalog, CustomerCart $cart): void
    {
        $this->putInCart($catalog, $cart, $menuId);
    }

    public function incrementQty(string $menuId, CustomerCart $cart): void
    {
        $current = $cart->qtyOf($this->tableId, $menuId);

        if ($current > 0) {
            $cart->setQty($this->tableId, $menuId, $current + 1);
        }
    }

    public function decrementQty(string $menuId, CustomerCart $cart): void
    {
        if ($cart->qtyOf($this->tableId, $menuId) <= 1) {
            $cart->removeItem($this->tableId, $menuId);

            return;
        }

        $cart->setQty($this->tableId, $menuId, $cart->qtyOf($this->tableId, $menuId) - 1);
    }

    public function removeItem(string $menuId, CustomerCart $cart): void
    {
        $cart->removeItem($this->tableId, $menuId);
    }

    public function clearCart(CustomerCart $cart): void
    {
        $cart->empty($this->tableId);
    }

    public function checkout(PlaceCustomerOrderUseCase $placeOrder, CustomerCart $cart)
    {
        try {
            $placeOrder->handle(new PlaceCustomerOrderData(
                items: $cart->toOrderItems($this->tableId),
                tableId: $this->tableId,
                notes: $this->orderNotes,
            ));
        } catch (ValidationException $e) {
            $this->addError('cart', $e->validator->errors()->first());

            return null;
        }

        $cart->empty($this->tableId);
        $this->orderNotes = '';
        session()->flash('success', 'Pesanan terkirim ke dapur. Anda bisa memesan lagi bila perlu.');

        // Stay at the same table so the party can place more rounds.
        return $this->redirectRoute('customer.menus.index', ['table_id' => $this->tableId], navigate: true);
    }

    public function render(GetMenuCatalogQueryUseCase $catalog, FindTableQueryUseCase $findTable, CustomerCart $cart)
    {
        return view('livewire.customer.menu-order', [
            'menus' => $catalog->paginate($this->search, $this->activeCategoryId, 24),
            'categories' => $catalog->categories(),
            'totalAvailable' => $catalog->countAvailable(),
            'table' => $findTable->byId($this->tableId),
            'cartItems' => $cart->items($this->tableId),
            'cartCount' => $cart->count($this->tableId),
            'cartSubtotal' => $cart->subtotal($this->tableId),
        ]);
    }

    /**
     * The cart stores a price snapshot, so every add has to go through the
     * catalog first — and that is also where an unavailable menu is caught.
     */
    private function putInCart(
        GetMenuCatalogQueryUseCase $catalog,
        CustomerCart $cart,
        string $menuId,
        int $qty = 1,
        ?string $notes = null,
    ): void {
        $menu = $catalog->find($menuId);

        if (! $menu || ! $menu->is_available) {
            $this->addError('cart', 'Menu tidak ditemukan atau sedang tidak tersedia.');

            return;
        }

        $cart->addItem($this->tableId, [
            'id' => (string) $menu->id,
            'name' => (string) $menu->name,
            'price' => (float) $menu->price,
            'image_url' => $menu->image_url,
        ], $qty, $notes);
    }
}
