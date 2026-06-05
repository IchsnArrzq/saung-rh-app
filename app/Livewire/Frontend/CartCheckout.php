<?php

namespace App\Livewire\Frontend;

use App\Models\Order;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\TableStatus;
use App\Support\RestaurantCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class CartCheckout extends Component
{
    public string $mode = RestaurantCart::MODE_ONLINE;

    public ?string $tableId = null;

    public ?string $reservationAt = null;

    public int $pax = 2;

    public string $customerName = '';

    public string $phone = '';

    public string $notes = '';

    public function mount(): void
    {
        $context = RestaurantCart::syncContextFromRequest(request());

        $this->mode = $context['mode'];
        $this->tableId = $context['table_id'];
        $this->customerName = Auth::user()?->name ?? '';
    }

    public function setMode(string $mode): void
    {
        $context = RestaurantCart::setMode($mode);
        $this->mode = $context['mode'];
        $this->tableId = $context['table_id'];
    }

    public function selectTable(string $tableId): void
    {
        $this->tableId = $tableId;

        if ($this->mode === RestaurantCart::MODE_OFFLINE) {
            RestaurantCart::setTableId($tableId);
        }
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

        if ($this->mode === RestaurantCart::MODE_OFFLINE) {
            return $this->checkoutOffline($cart);
        }

        return $this->checkoutOnline($cart);
    }

    private function checkoutOnline(array $cart)
    {
        $this->validate([
            'tableId' => ['required', 'exists:tables,id'],
            'reservationAt' => ['required', 'date', 'after:now'],
            'pax' => ['required', 'integer', 'min:1', 'max:30'],
            'customerName' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($cart): void {
            $reservation = Reservation::query()->create([
                'user_id' => Auth::id(),
                'table_id' => $this->tableId,
                'customer_name' => $this->customerName,
                'phone' => $this->phone ?: null,
                'pax' => $this->pax,
                'reservation_at' => $this->reservationAt,
                'status' => 'pending',
                'notes' => $this->notes ?: null,
            ]);

            $items = collect($cart)->map(function (array $item): array {
                $price = (float) $item['price'];
                $qty = (int) $item['qty'];

                return [
                    'menu_id' => $item['menu_id'],
                    'menu_name_snapshot' => $item['name'],
                    'qty' => $qty,
                    'unit_price' => $price,
                    'line_total' => $qty * $price,
                    'notes' => $item['notes'] ?? null,
                ];
            })->values()->all();

            $reservation->items()->createMany($items);
        });

        RestaurantCart::clearCart();
        RestaurantCart::setMode(RestaurantCart::MODE_ONLINE);

        session()->flash('success', 'Booking online berhasil dibuat.');

        return $this->redirectRoute('public.menu', navigate: true);
    }

    private function checkoutOffline(array $cart)
    {
        $this->validate([
            'tableId' => ['required', 'exists:tables,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $table = Table::query()->with('tableStatus')->findOrFail($this->tableId);
        $orderInStatus = TableStatus::query()->where('key', 'order_in')->first();

        DB::transaction(function () use ($cart, $table, $orderInStatus): void {
            $subtotal = collect($cart)->sum(fn (array $item) => ((float) $item['price']) * ((int) $item['qty']));

            $order = Order::query()->create([
                'table_id' => $table->id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => 'Offline QR - '.$table->code,
                'status' => 'confirmed',
                'notes' => trim('Sumber: OFFLINE QR | '.($this->notes ?: '')),
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

            if ($table->tableStatus?->key === 'available' && $orderInStatus) {
                $table->update([
                    'table_status_id' => $orderInStatus->id,
                    'status' => $orderInStatus->key,
                ]);
            }
        });

        RestaurantCart::clearCart();
        RestaurantCart::setTableId($table->id);

        session()->flash('success', 'Pesanan offline berhasil dikirim ke admin.');

        return $this->redirectRoute('public.menu', [
            'mode' => RestaurantCart::MODE_OFFLINE,
            'table_id' => $table->id,
        ], navigate: true);
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
