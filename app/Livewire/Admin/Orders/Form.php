<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    /**
     * @var array<int, string>
     */
    private const STATUS_OPTIONS = ['draft', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'];

    public ?Order $order = null;

    public string $table_id = '';

    public string $customer_name = '';

    public string $status = 'draft';

    public string $ordered_at = '';

    public string $notes = '';

    public string $tax = '0';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public function mount(?Order $order = null): void
    {
        $this->order = $order?->load('items');

        if ($this->order) {
            $this->authorize('update', $this->order);

            $this->table_id = (string) ($this->order->table_id ?? '');
            $this->customer_name = (string) ($this->order->customer_name ?? '');
            $this->status = (string) ($this->order->status ?: 'draft');
            $this->ordered_at = (string) ($this->order->ordered_at?->format('Y-m-d\TH:i') ?? '');
            $this->notes = (string) ($this->order->notes ?? '');
            $this->tax = (string) ($this->order->tax ?? 0);
            $this->items = $this->order->items->map(function ($item): array {
                return [
                    'menu_id' => (string) ($item->menu_id ?? ''),
                    'menu_name_snapshot' => (string) ($item->menu_name_snapshot ?? ''),
                    'qty' => (int) $item->qty,
                    'price' => (float) $item->price,
                    'notes' => (string) ($item->notes ?? ''),
                ];
            })->toArray();

            if ($this->items === []) {
                $this->items = [$this->emptyItem()];
            }

            return;
        }

        $this->authorize('create', Order::class);
        $this->ordered_at = now()->format('Y-m-d\TH:i');
        $this->items = [$this->emptyItem()];
    }

    public function addItem(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function applyMenu(int $index): void
    {
        $menuId = (string) ($this->items[$index]['menu_id'] ?? '');

        if ($menuId === '') {
            return;
        }

        $menu = Menu::query()->find($menuId);

        if (! $menu) {
            return;
        }

        $this->items[$index]['menu_name_snapshot'] = (string) $menu->name;
        $this->items[$index]['price'] = (float) $menu->price;
    }

    public function save()
    {
        $validated = $this->validate($this->rules());
        $normalizedItems = $this->normalizeItems($validated['items']);
        $subtotal = collect($normalizedItems)->sum('line_total');
        $tax = (float) ($validated['tax'] ?? 0);
        $total = max($subtotal + $tax, 0);

        DB::transaction(function () use ($validated, $normalizedItems, $subtotal, $tax, $total): void {
            if ($this->order) {
                $this->order->update([
                    'table_id' => $validated['table_id'] ?: null,
                    'customer_name' => $validated['customer_name'] ?: null,
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?: null,
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'tax' => $tax,
                    'total' => $total,
                    'ordered_at' => $validated['ordered_at'] ?: $this->order->ordered_at,
                ]);

                $this->order->items()->delete();
                $this->order->items()->createMany($normalizedItems);

                return;
            }

            $order = Order::query()->create([
                'table_id' => $validated['table_id'] ?: null,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $validated['customer_name'] ?: null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?: null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => $tax,
                'total' => $total,
                'ordered_at' => $validated['ordered_at'] ?: now(),
            ]);

            $order->items()->createMany($normalizedItems);
        });

        session()->flash('success', $this->order ? 'Order berhasil diperbarui.' : 'Order berhasil dibuat.');

        return $this->redirectRoute('orders.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'ordered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['nullable', 'exists:menus,id'],
            'items.*.menu_name_snapshot' => ['required', 'string', 'max:150'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        return collect($items)->map(function (array $item): array {
            $qty = (int) $item['qty'];
            $price = (float) $item['price'];

            return [
                'menu_id' => ($item['menu_id'] ?? '') !== '' ? $item['menu_id'] : null,
                'menu_name_snapshot' => $item['menu_name_snapshot'],
                'qty' => $qty,
                'price' => $price,
                'line_total' => $qty * $price,
                'notes' => ($item['notes'] ?? '') !== '' ? $item['notes'] : null,
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyItem(): array
    {
        return [
            'menu_id' => '',
            'menu_name_snapshot' => '',
            'qty' => 1,
            'price' => 0,
            'notes' => '',
        ];
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    /**
     * @return Collection<int, Table>
     */
    public function tables(): Collection
    {
        return Table::query()->orderBy('code')->get();
    }

    /**
     * @return Collection<int, Menu>
     */
    public function menus(): Collection
    {
        return Menu::query()->where('is_available', true)->orderBy('name')->get();
    }

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function render(): View
    {
        return view('livewire.admin.orders.form', [
            'tables' => $this->tables(),
            'menus' => $this->menus(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }
}
