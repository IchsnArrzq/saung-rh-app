<?php

namespace App\Livewire\Frontend;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableStatus;
use App\Support\RestaurantCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function checkout()
    {
        $cart = RestaurantCart::cart();

        if (empty($cart)) {
            $this->addError('cart', 'Cart masih kosong.');

            return null;
        }

        $this->validate([
            'tableId' => ['required', 'exists:tables,id'],
            'customerName' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        $table = Table::query()->with('tableStatus')->findOrFail($this->tableId);
        $orderInStatus = TableStatus::query()->where('key', 'order_in')->first();

        $order = DB::transaction(function () use ($cart, $table, $orderInStatus): Order {
            $subtotal = collect($cart)->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));

            $order = Order::query()->create([
                'table_id' => $table->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $this->customerName !== '' ? $this->customerName : 'Tamu Meja '.$table->code,
                'status' => 'confirmed',
                'notes' => trim('Sumber: DINE-IN QR | '.($this->notes ?: '')),
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'ordered_at' => now(),
            ]);

            $items = collect($cart)->map(function (array $item): array {
                $price = (float) $item['price'];
                $qty = (int) $item['qty'];

                return [
                    'menu_id' => $item['menu_id'],
                    'menu_name_snapshot' => $item['name'],
                    'qty' => $qty,
                    'price' => $price,
                    'line_total' => $qty * $price,
                    'notes' => $item['notes'] ?? null,
                ];
            })->values()->all();

            $order->items()->createMany($items);

            // A QR check-in already flips the table to "occupied"; an incoming
            // order should advance either state to "order_in".
            if ($orderInStatus && in_array($table->tableStatus?->key, ['available', 'occupied'], true)) {
                $table->update(['table_status_id' => $orderInStatus->id]);
            }

            return $order;
        });

        RestaurantCart::clearCart();
        RestaurantCart::setTableId($table->id);

        OrderCreated::dispatch($order);

        session()->flash('success', 'Pesanan berhasil dikirim ke dapur.');

        return $this->redirectRoute('public.menu', ['table_id' => $table->id], navigate: true);
    }

    public function render()
    {
        $tables = Table::query()
            ->with('tableStatus')
            ->whereHas('tableStatus', fn ($status) => $status->whereIn('key', ['available', 'occupied', 'order_in']))
            ->orderBy('code')
            ->get();

        return view('livewire.frontend.cart-checkout', [
            'cartItems' => collect(RestaurantCart::cart())->values(),
            'subtotal' => RestaurantCart::subtotal(),
            'tables' => $tables,
            'tableId' => $this->tableId,
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
