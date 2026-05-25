<?php

namespace App\Services\Admin;

use App\Events\OrderCreated;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderService
{
    public const STATUS_OPTIONS = ['draft', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'];

    public function paginate(int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $search = trim($search);

        return Order::query()
            ->with('table')
            ->withCount('items')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('table', fn ($table) => $table->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function tables(): Collection
    {
        return Table::query()->orderBy('code')->get();
    }

    public function availableMenus(): Collection
    {
        return Menu::query()->where('is_available', true)->orderBy('name')->get();
    }

    public function withItems(Order $order): Order
    {
        return $order->load('items');
    }

    public function create(Request $request): void
    {
        $validated = $this->validate($request);

        DB::transaction(function () use ($validated): void {
            $items = $this->normalizeItems($validated['items']);
            $subtotal = collect($items)->sum('line_total');
            $tax = (float) ($validated['tax'] ?? 0);
            $total = max($subtotal + $tax, 0);

            $order = Order::query()->create([
                'user_id' => Auth::id(),
                'table_id' => $validated['table_id'] ?? null,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $validated['customer_name'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => $tax,
                'total' => $total,
                'ordered_at' => $validated['ordered_at'] ?? now(),
            ]);

            $order->items()->createMany($items);
        });

        if ($order && in_array($order->status, ['confirmed', 'preparing'])) {
             OrderCreated::dispatch($order);
        }
    }

    public function update(Request $request, Order $order): void
    {
        $validated = $this->validate($request);

        DB::transaction(function () use ($validated, $order): void {
            $items = $this->normalizeItems($validated['items']);
            $subtotal = collect($items)->sum('line_total');
            $tax = (float) ($validated['tax'] ?? 0);
            $total = max($subtotal + $tax, 0);

            $order->update([
                'table_id' => $validated['table_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => $tax,
                'total' => $total,
                'ordered_at' => $validated['ordered_at'] ?? $order->ordered_at,
            ]);

            $order->items()->delete();
            $order->items()->createMany($items);
        });
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }

    private function validate(Request $request): array
    {
        return $request->validate([
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'ordered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['nullable', 'exists:menus,id'],
            'items.*.menu_name_snapshot' => ['nullable', 'string', 'max:150'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);
    }

    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->map(function (array $item): array {
                $qty = (int) $item['qty'];
                $price = (float) $item['price'];

                return [
                    'menu_id' => $item['menu_id'] ?: null,
                    'menu_name_snapshot' => $item['menu_name_snapshot'] ?: null,
                    'qty' => $qty,
                    'price' => $price,
                    'line_total' => $qty * $price,
                    'notes' => $item['notes'] ?: null,
                ];
            })
            ->values()
            ->all();
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
