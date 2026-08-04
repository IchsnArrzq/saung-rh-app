<?php

namespace App\Livewire\Customer;

use App\Models\Menu;
use App\Models\Table;
use App\Services\Customer\OrderCartService;
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

    public function mount(OrderCartService $service): void
    {
        $tableId = (string) request()->query('table_id', '');

        // Fall back to the seated table so "Pesan Menu" keeps working after the
        // first order moves the table to "order_in".
        if ($tableId === '') {
            $tableId = (string) $service->activeTableId();
        }

        if ($tableId === '' || ! $service->findOrderableTable($tableId)) {
            session()->flash('warning', 'Pilih meja terlebih dahulu untuk mulai memesan.');
            $this->redirectRoute('customer.menus.tables', navigate: true);

            return;
        }

        $this->tableId = $tableId;
        $service->setActiveTable($tableId);
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

    public function addFromDetail(OrderCartService $service): void
    {
        if (! $this->selectedMenu) {
            return;
        }

        $service->addItem($this->tableId, $this->selectedMenu['id'], max(1, $this->detailQty), $this->detailNotes);

        $this->closeMenuDetail();
        session()->flash('success', 'Menu ditambahkan ke cart.');
    }

    public function quickAdd(string $menuId, OrderCartService $service): void
    {
        $service->addItem($this->tableId, $menuId, 1);
    }

    public function incrementQty(string $menuId, OrderCartService $service): void
    {
        $current = $this->currentQty($service, $menuId);

        if ($current > 0) {
            $service->setQty($this->tableId, $menuId, $current + 1);
        }
    }

    public function decrementQty(string $menuId, OrderCartService $service): void
    {
        $current = $this->currentQty($service, $menuId);

        if ($current <= 1) {
            $service->removeItem($this->tableId, $menuId);

            return;
        }

        $service->setQty($this->tableId, $menuId, $current - 1);
    }

    public function removeItem(string $menuId, OrderCartService $service): void
    {
        $service->removeItem($this->tableId, $menuId);
    }

    public function clearCart(OrderCartService $service): void
    {
        $service->emptyCart($this->tableId);
    }

    private function currentQty(OrderCartService $service, string $menuId): int
    {
        $item = $service->cartItems($this->tableId)->firstWhere('menu_id', $menuId);

        return (int) ($item['qty'] ?? 0);
    }

    public function checkout(OrderCartService $service)
    {
        try {
            $service->placeOrder($this->tableId, $this->orderNotes);
        } catch (ValidationException $e) {
            $this->addError('cart', $e->validator->errors()->first());

            return null;
        }

        $this->orderNotes = '';
        session()->flash('success', 'Pesanan terkirim ke dapur. Anda bisa memesan lagi bila perlu.');

        // Stay at the same table so the party can place more rounds.
        return $this->redirectRoute('customer.menus.index', ['table_id' => $this->tableId], navigate: true);
    }

    public function render(OrderCartService $service)
    {
        $catalog = $service->catalog($this->search, $this->activeCategoryId);

        return view('livewire.customer.menu-order', [
            'menus' => $catalog['menus'],
            'categories' => $catalog['categories'],
            'totalAvailable' => $catalog['totalAvailable'],
            'table' => Table::query()->find($this->tableId),
            'cartItems' => $service->cartItems($this->tableId),
            'cartCount' => $service->cartCount($this->tableId),
            'cartSubtotal' => $service->cartSubtotal($this->tableId),
        ]);
    }
}
