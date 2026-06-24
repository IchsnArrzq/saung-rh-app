<?php

namespace App\Livewire\Admin\Reservations;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\TableStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $selectedReservation = null;

    public function mount(): void
    {
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        $reservation = Reservation::query()->findOrFail($id);
        $reservation->delete();

        session()->flash('success', 'Reservasi berhasil dihapus.');
    }

    public function showDetail(string $id): void
    {
        $reservation = Reservation::query()
            ->with(['table', 'user', 'items.menu'])
            ->findOrFail($id);

        $this->selectedReservation = [
            'id' => (string) $reservation->id,
            'customer_name' => (string) $reservation->customer_name,
            'phone' => (string) ($reservation->phone ?? '-'),
            'table' => (string) ($reservation->table?->code ?? '-'),
            'pax' => (int) $reservation->pax,
            'reservation_at' => (string) ($reservation->reservation_at?->format('d M Y H:i') ?? '-'),
            'status' => (string) str_replace('_', ' ', $reservation->status),
            'notes' => (string) ($reservation->notes ?? ''),
            'subtotal' => (float) $reservation->items->sum('line_total'),
            'items' => $reservation->items
                ->map(fn ($item): array => [
                    'name' => (string) $item->menu_name_snapshot,
                    'qty' => (int) $item->qty,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'notes' => (string) ($item->notes ?? ''),
                ])
                ->values()
                ->all(),
        ];

        $this->dispatch('open-modal', 'reservation-detail-modal');
    }

    public function generateOrder(string $id): void
    {
        $reservation = Reservation::query()
            ->with(['table.tableStatus', 'items'])
            ->findOrFail($id);

        if ($reservation->items->isEmpty()) {
            $this->addError('reservation', 'Reservasi belum memiliki item menu.');

            return;
        }

        $sourceMarker = 'Reservation ID: '.$reservation->id;
        $existingOrder = Order::query()
            ->where('notes', 'like', '%'.$sourceMarker.'%')
            ->first();

        if ($existingOrder) {
            session()->flash('success', 'Order '.$existingOrder->order_number.' sudah pernah dibuat dari reservasi ini.');

            return;
        }

        $orderInStatus = TableStatus::query()->where('key', 'order_in')->first();

        $order = DB::transaction(function () use ($reservation, $sourceMarker, $orderInStatus): Order {
            $subtotal = (float) $reservation->items->sum('line_total');

            $order = Order::query()->create([
                'user_id' => auth()->id(),
                'table_id' => $reservation->table_id,
                'order_number' => $this->generateOrderNumber(),
                'customer_name' => $reservation->customer_name,
                'status' => 'confirmed',
                'notes' => trim('Sumber: RESERVATION | '.$sourceMarker.' | '.($reservation->notes ?? '')),
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal,
                'ordered_at' => now(),
            ]);

            $order->items()->createMany($reservation->items
                ->map(fn ($item): array => [
                    'menu_id' => $item->menu_id,
                    'menu_name_snapshot' => $item->menu_name_snapshot,
                    'qty' => (int) $item->qty,
                    'price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'notes' => $item->notes,
                    'status' => 'pending',
                ])
                ->values()
                ->all());

            $reservation->update(['status' => 'seated']);

            $table = $reservation->table;
            if ($table && $table->tableStatus?->key === 'available' && $orderInStatus) {
                $table->update([
                    'table_status_id' => $orderInStatus->id,
                ]);
            }

            return $order;
        });

        OrderCreated::dispatch($order);

        session()->flash('success', 'Order '.$order->order_number.' berhasil dibuat dari reservasi.');
    }

    public function render(): View
    {
        $search = trim($this->search);

        $reservations = Reservation::query()
            ->with('table')
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%')
                        ->orWhereHas('table', fn (Builder $table) => $table->where('code', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.admin.reservations.table', [
            'reservations' => $reservations,
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
