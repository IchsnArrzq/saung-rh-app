<?php

namespace App\Livewire\Pos;

use App\Events\OrderCreated;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\TableStatus;
use App\Support\RestaurantCart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function addToCart(string $menuId): void
    {
        $menu = Menu::query()
            ->available()
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

    public function placeOrder(): void
    {
        $this->tableId = is_string($this->tableId) && trim($this->tableId) === '' ? null : $this->tableId;

        $validated = $this->validate([
            'tableId' => ['nullable', 'exists:tables,id'],
            'customerName' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'payNow' => ['boolean'],
            'paymentMethod' => ['required_if:payNow,true', 'in:cash,qris,debit_card,credit_card,transfer,ewallet'],
        ]);

        $cart = RestaurantCart::cart();

        if ($cart === []) {
            $this->addError('cart', 'Order masih kosong.');

            return;
        }

        $orderInStatus = TableStatus::query()->where('key', 'order_in')->first();

        $order = DB::transaction(function () use ($cart, $validated, $orderInStatus): Order {
            $table = null;
            $tableId = trim((string) ($validated['tableId'] ?? ''));

            if ($tableId !== '') {
                $table = Table::query()->with('tableStatus')->find($tableId);
            }

            $subtotal = (float) collect($cart)
                ->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));
            $cleanNotes = trim((string) ($validated['notes'] ?? ''));

            $order = Order::query()->create([
                'cashier_id' => auth()->id(),
                'table_id' => $table?->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => trim((string) ($validated['customerName'] ?? '')) ?: null,
                'status' => 'confirmed',
                'notes' => $cleanNotes !== '' ? 'Sumber: POS | '.$cleanNotes : 'Sumber: POS',
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'ordered_at' => now(),
            ]);

            $items = collect($cart)
                ->map(function (array $item): array {
                    $qty = (int) $item['qty'];
                    $price = (float) $item['price'];

                    return [
                        'menu_id' => $item['menu_id'],
                        'menu_name_snapshot' => $item['name'],
                        'qty' => $qty,
                        'price' => $price,
                        'line_total' => $qty * $price,
                        'notes' => $item['notes'] ?? null,
                    ];
                })
                ->values()
                ->all();

            $order->items()->createMany($items);

            if ((bool) ($validated['payNow'] ?? false) && $subtotal > 0) {
                Payment::query()->create([
                    'order_id' => $order->id,
                    'method' => $validated['paymentMethod'] ?? 'cash',
                    'type' => 'full',
                    'status' => 'paid',
                    'amount' => $subtotal,
                    'reference' => 'POS-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
                    'notes' => 'Payment otomatis dari POS.',
                    'paid_at' => now(),
                ]);
            }

            if ($table && $table->tableStatus?->key === 'available' && $orderInStatus) {
                $table->update([
                    'table_status_id' => $orderInStatus->id,
                ]);
            }

            return $order;
        });

        RestaurantCart::clearCart();
        $this->notes = '';
        $this->customerName = (string) (auth()->user()?->name ?? '');
        $this->tableId = null;
        $this->payNow = true;
        $this->paymentMethod = 'cash';
        $this->resetValidation();

        OrderCreated::dispatch($order);

        session()->flash('success', 'Order '.$order->order_number.' berhasil disimpan.');
    }

    public function getCategoriesProperty(): Collection
    {
        return MenuCategory::query()
            ->where('is_active', true)
            ->withCount(['menus' => function ($query) {
                $query->available();
            }])
            ->orderBy('name')
            ->get();
    }

    public function getMenusProperty(): Collection
    {
        $search = trim($this->search);

        return Menu::query()
            ->with('category:id,name')
            ->available()
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

    public function getTablesProperty(): Collection
    {
        return Table::query()
            ->with('tableStatus:id,key,name')
            ->orderBy('code')
            ->get();
    }

    public function render()
    {
        return view('livewire.pos.order-card', [
            'categories' => $this->categories,
            'menus' => $this->menus,
            'totalAvailableMenus' => Menu::query()->available()->count(),
            'cartItems' => $this->cartItems,
            'cartCount' => $this->cartCount,
            'cartSubtotal' => $this->cartSubtotal,
            'tables' => $this->tables,
        ]);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
