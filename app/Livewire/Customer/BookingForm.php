<?php

namespace App\Livewire\Customer;

use App\Models\Menu;
use App\Services\Customer\BookingService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['portal' => 'customer'])]
class BookingForm extends Component
{
    public ?string $table_id = null;

    public int $pax = 2;

    public string $reservation_at = '';

    public string $notes = '';

    /** @var array<int,array{menu_id:string,name:string,price:float,image_url:?string,qty:int,notes:string}> */
    public array $items = [];

    public string $search = '';

    public ?int $activeCategory = null;

    public function addItem(string $menuId): void
    {
        foreach ($this->items as $index => $item) {
            if ($item['menu_id'] === $menuId) {
                $this->items[$index]['qty'] = min(20, $item['qty'] + 1);

                return;
            }
        }

        $menu = Menu::query()->available()->find($menuId);

        if (! $menu) {
            return;
        }

        $this->items[] = [
            'menu_id' => (string) $menu->id,
            'name' => (string) $menu->name,
            'price' => (float) $menu->price,
            'image_url' => $menu->image_url,
            'qty' => 1,
            'notes' => '',
        ];
    }

    public function increment(int $index): void
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['qty'] = min(20, $this->items[$index]['qty'] + 1);
        }
    }

    public function decrement(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        if ($this->items[$index]['qty'] <= 1) {
            $this->removeItem($index);

            return;
        }

        $this->items[$index]['qty']--;
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function resetItems(): void
    {
        $this->items = [];
    }

    public function setCategory(?int $categoryId = null): void
    {
        $this->activeCategory = $categoryId;
    }

    public function submit(BookingService $service)
    {
        $validated = $this->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'pax' => ['required', 'integer', 'min:1', 'max:30'],
            'reservation_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.notes' => ['nullable', 'string'],
        ], [], [
            'items' => 'menu pesanan',
            'table_id' => 'meja',
            'reservation_at' => 'waktu reservasi',
        ]);

        try {
            $service->place($validated);
        } catch (ValidationException $e) {
            $this->addError('table_id', $e->validator->errors()->first());

            return null;
        }

        session()->flash('success', 'Reservasi berhasil dibuat. Tim kami akan segera mengonfirmasi booking Anda.');

        return $this->redirectRoute('customer.dashboard', navigate: true);
    }

    public function render(BookingService $service)
    {
        $data = $service->createFormData();

        $search = trim(mb_strtolower($this->search));

        $menus = $data['menus']
            ->when($this->activeCategory, fn ($menus) => $menus->where('menu_category_id', $this->activeCategory))
            ->when($search !== '', fn ($menus) => $menus->filter(
                fn ($menu) => str_contains(mb_strtolower((string) $menu->name), $search)
            ))
            ->values();

        return view('livewire.customer.booking-form', [
            'tables' => $data['tables'],
            'categories' => $data['categories'],
            'menus' => $menus,
            'totalMenus' => $data['menus']->count(),
            'subtotal' => collect($this->items)->sum(fn ($item) => $item['price'] * $item['qty']),
            'totalQty' => collect($this->items)->sum('qty'),
        ]);
    }
}
