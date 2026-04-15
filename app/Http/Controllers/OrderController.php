<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const STATUS_OPTIONS = ['draft', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'];

    public function index(): View
    {
        $orders = Order::query()
            ->with('table')
            ->withCount('items')
            ->latest()
            ->paginate(12);

        return view('admin.orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('admin.orders.create', [
            'tables' => Table::query()->orderBy('code')->get(),
            'menus' => Menu::query()->where('is_available', true)->orderBy('name')->get(),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateOrder($request);

        DB::transaction(function () use ($validated) {
            $items = $this->normalizeItems($validated['items']);
            $subtotal = collect($items)->sum('line_total');
            $discount = (float) ($validated['discount'] ?? 0);
            $tax = (float) ($validated['tax'] ?? 0);
            $total = max($subtotal - $discount + $tax, 0);

            $order = Order::create([
                'table_id' => $validated['table_id'] ?? null,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $validated['customer_name'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'ordered_at' => $validated['ordered_at'] ?? now(),
            ]);

            $order->items()->createMany($items);
        });

        return redirect()->route('orders.index')->with('success', 'Order berhasil dibuat.');
    }

    public function edit(Order $order): View
    {
        $order->load('items');

        return view('admin.orders.edit', [
            'order' => $order,
            'tables' => Table::query()->orderBy('code')->get(),
            'menus' => Menu::query()->where('is_available', true)->orderBy('name')->get(),
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $this->validateOrder($request);

        DB::transaction(function () use ($validated, $order) {
            $items = $this->normalizeItems($validated['items']);
            $subtotal = collect($items)->sum('line_total');
            $discount = (float) ($validated['discount'] ?? 0);
            $tax = (float) ($validated['tax'] ?? 0);
            $total = max($subtotal - $discount + $tax, 0);

            $order->update([
                'table_id' => $validated['table_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'ordered_at' => $validated['ordered_at'] ?? $order->ordered_at,
            ]);

            $order->items()->delete();
            $order->items()->createMany($items);
        });

        return redirect()->route('orders.index')->with('success', 'Order berhasil diperbarui.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('orders.index')->with('success', 'Order berhasil dihapus.');
    }

    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'ordered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'discount' => ['nullable', 'numeric', 'min:0'],
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
