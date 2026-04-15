<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = ['cash', 'qris', 'debit_card', 'credit_card', 'transfer', 'ewallet'];
        $statuses = ['pending', 'paid', 'failed', 'refunded'];

        Order::query()->with('payments')->chunk(50, function ($orders) use ($methods, $statuses): void {
            foreach ($orders as $order) {
                if ($order->payments->isNotEmpty()) {
                    continue;
                }

                $paymentType = 'full';
                $status = $statuses[array_rand($statuses)];

                $order->payments()->create([
                    'method' => $methods[array_rand($methods)],
                    'type' => $paymentType,
                    'status' => $status,
                    'amount' => (float) $order->total,
                    'reference' => strtoupper(fake()->bothify('PAY-####??')),
                    'notes' => fake()->boolean(20) ? fake()->sentence(6) : null,
                    'paid_at' => $status === 'paid' ? ($order->ordered_at ?? now()) : null,
                ]);
            }
        });
    }
}
