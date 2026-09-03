<?php

namespace App\Livewire\Admin\Orders;

use App\Domains\Order\DTO\CreateOrderData;
use App\Domains\Order\DTO\UpdateOrderData;
use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\UseCases\CreateOrderUseCase;
use App\Domains\Order\UseCases\UpdateOrderUseCase;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{
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
        $this->order = $order?->exists ? $order->load('items') : null;

        $this->authorizeWrite();

        if ($this->order) {

            $this->table_id = (string) ($this->order->table_id ?? '');
            $this->customer_name = (string) ($this->order->customer_name ?? '');
            $this->status = ($this->order->status ?? OrderStatus::Draft)->value;
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

        $this->ordered_at = now()->format('Y-m-d\TH:i');
        $this->items = [$this->emptyItem()];
    }

    /**
     * Satu form melayani dua ability, dan dipanggil dua kali: di mount() untuk
     * menutup halamannya, lalu di save() karena mount() hanya jalan sekali
     * sedangkan save() adalah request HTTP tersendiri.
     */
    private function authorizeWrite(): void
    {
        $this->order
            ? $this->authorize('update', $this->order)
            : $this->authorize('create', Order::class);
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

    public function save(CreateOrderUseCase $createOrder, UpdateOrderUseCase $updateOrder)
    {
        $this->authorizeWrite();

        $validated = $this->validate($this->rules());

        $status = OrderStatus::from($validated['status']);
        $tableId = $validated['table_id'] ?: null;
        $customerName = $validated['customer_name'] ?: null;
        $notes = $validated['notes'] ?: null;
        $tax = (float) ($validated['tax'] ?? 0);
        $orderedAt = $validated['ordered_at'] ?: null;

        if ($this->order) {
            $updateOrder->handle($this->order, new UpdateOrderData(
                items: $validated['items'],
                status: $status,
                tableId: $tableId,
                customerName: $customerName,
                notes: $notes,
                tax: $tax,
                orderedAt: $orderedAt,
            ));
        } else {
            $createOrder->handle(new CreateOrderData(
                items: $validated['items'],
                status: $status,
                tableId: $tableId,
                customerName: $customerName,
                notes: $notes,
                tax: $tax,
                orderedAt: $orderedAt,
            ));
        }

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
            'status' => ['required', Rule::in(OrderStatus::values())],
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
        return Menu::query()->available()->orderBy('name')->get();
    }

    /**
     * @return array<int, string>
     */
    public function statusOptions(): array
    {
        return OrderStatus::values();
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
