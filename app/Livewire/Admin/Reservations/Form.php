<?php

namespace App\Livewire\Admin\Reservations;

use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Form extends Component
{

    /**
     * @var array<int, string>
     */
    private const STATUS_OPTIONS = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];

    public ?Reservation $reservation = null;

    public string $table_id = '';

    public string $customer_name = '';

    public string $phone = '';

    public string $pax = '1';

    public string $reservation_at = '';

    public string $status = 'pending';

    public string $notes = '';

    /**
     * @var array<int, array{menu_id:string,qty:int|string,notes:string}>
     */
    public array $items = [
        ['menu_id' => '', 'qty' => 1, 'notes' => ''],
    ];

    public function mount(?Reservation $reservation = null): void
    {
        $this->reservation = $reservation;

        if ($this->reservation) {
            $this->reservation->loadMissing('items');

            $this->table_id = (string) ($this->reservation->table_id ?? '');
            $this->customer_name = (string) $this->reservation->customer_name;
            $this->phone = (string) ($this->reservation->phone ?? '');
            $this->pax = (string) $this->reservation->pax;
            $this->reservation_at = (string) ($this->reservation->reservation_at?->format('Y-m-d\TH:i') ?? '');
            $this->status = (string) ($this->reservation->status ?: 'pending');
            $this->notes = (string) ($this->reservation->notes ?? '');
            $this->items = $this->reservation->items
                ->map(fn ($item): array => [
                    'menu_id' => (string) ($item->menu_id ?? ''),
                    'qty' => (int) $item->qty,
                    'notes' => (string) ($item->notes ?? ''),
                ])
                ->values()
                ->all() ?: [['menu_id' => '', 'qty' => 1, 'notes' => '']];

            return;
        }

        $this->reservation_at = now()->addHour()->format('Y-m-d\TH:i');
    }

    public function addItem(): void
    {
        $this->items[] = ['menu_id' => '', 'qty' => 1, 'notes' => ''];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $validated = $this->validate($this->rules());
        $validated['table_id'] = $validated['table_id'] ?: null;
        $validated['phone'] = $validated['phone'] ?: null;
        $validated['notes'] = $validated['notes'] ?: null;

        DB::transaction(function () use ($validated): void {
            $itemPayload = $validated['items'];
            unset($validated['items']);

            $reservation = $this->reservation;

            if ($reservation) {
                $reservation->update($validated);
            } else {
                $reservation = Reservation::query()->create($validated);
            }

            $menuMap = Menu::query()
                ->whereIn('id', collect($itemPayload)->pluck('menu_id')->filter())
                ->get()
                ->keyBy('id');

            $items = collect($itemPayload)
                ->map(function (array $item) use ($menuMap): array {
                    $menu = $menuMap->get($item['menu_id']);
                    $qty = (int) $item['qty'];
                    $price = (float) ($menu?->price ?? 0);

                    return [
                        'menu_id' => $menu?->id,
                        'menu_name_snapshot' => $menu?->name ?? 'Unknown Menu',
                        'qty' => $qty,
                        'unit_price' => $price,
                        'line_total' => $qty * $price,
                        'notes' => trim((string) ($item['notes'] ?? '')) ?: null,
                    ];
                })
                ->values()
                ->all();

            $reservation->items()->delete();
            $reservation->items()->createMany($items);
        });

        session()->flash('success', $this->reservation ? 'Reservasi berhasil diperbarui.' : 'Reservasi berhasil ditambahkan.');

        return $this->redirectRoute('reservations.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'table_id' => ['nullable', 'exists:tables,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'pax' => ['required', 'integer', 'min:1'],
            'reservation_at' => ['required', 'date'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'exists:menus,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
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
     * @return array<int, string>
     */
    public function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function menus(): Collection
    {
        return Menu::query()->where('is_available', true)->orderBy('name')->get();
    }

    public function render(): View
    {
        return view('livewire.admin.reservations.form', [
            'tables' => $this->tables(),
            'statusOptions' => $this->statusOptions(),
            'menus' => $this->menus(),
        ]);
    }
}
