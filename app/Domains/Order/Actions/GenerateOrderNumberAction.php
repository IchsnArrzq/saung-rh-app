<?php

namespace App\Domains\Order\Actions;

use App\Domains\Order\Repositories\OrderRepository;
use Illuminate\Support\Str;

/**
 * Produces a unique human-readable order number (ORD-YYYYMMDD-XXXX).
 *
 * Earns its own Action emphatically: the same do/while loop was copy-pasted
 * into six places (admin order form, reservation board, guest checkout, POS
 * order card, customer cart service and the now-dead OrderService) — and one
 * of those copies had silently drifted to a different random scheme.
 */
class GenerateOrderNumberAction
{
    public function __construct(private readonly OrderRepository $orders) {}

    public function handle(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while ($this->orders->orderNumberExists($number));

        return $number;
    }
}
