<?php

namespace App\Domains\Order\UseCases;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Hard-deletes an order from the admin listing.
 *
 * Refuses an order that carries money. A settled ticket is an accounting
 * record — the reports, the cashier's open-bill list and the stock ledger all
 * reconcile against it, and deleting the order would leave those payments
 * pointing at nothing. Cancel it instead (ChangeOrderStatusUseCase), which
 * keeps the trail intact.
 */
class DeleteOrderUseCase
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function handle(string $orderId): void
    {
        $order = $this->orders->find($orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'delete' => 'Order tidak ditemukan.',
            ]);
        }

        if ($order->payments()->exists()) {
            throw ValidationException::withMessages([
                'delete' => 'Order ini sudah punya catatan pembayaran dan tidak bisa dihapus. Batalkan pesanannya saja.',
            ]);
        }

        // Marked paid without a payment row — an anomaly the admin form still
        // allows. Treat it as money-bearing rather than quietly deleting it.
        if ($order->status === OrderStatus::Paid) {
            throw ValidationException::withMessages([
                'delete' => 'Order berstatus Lunas tidak bisa dihapus. Batalkan pesanannya saja.',
            ]);
        }

        DB::transaction(fn () => $this->orders->delete($order));
    }
}
