<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Order::query()->count() >= 20) {
            return;
        }

        $tableIds = Table::query()->pluck('id')->all();
        $customers = User::query()->role('customer')->get();
        $statuses = ['draft', 'confirmed', 'preparing', 'ready', 'served', 'paid', 'cancelled'];

        for ($i = 1; $i <= 20; $i++) {
            $orderDate = now()->subDays(rand(0, 10))->subMinutes(rand(0, 1440));
            $orderNumber = 'ORD-'.$orderDate->format('Ymd').'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

            // ~75% of orders belong to a known customer (the rest are walk-ins).
            $customer = $customers->isNotEmpty() && fake()->boolean(75) ? $customers->random() : null;

            Order::query()->firstOrCreate(
                ['order_number' => $orderNumber],
                [
                    'table_id' => ! empty($tableIds) ? $tableIds[array_rand($tableIds)] : null,
                    'customer_id' => $customer?->id,
                    'customer_name' => $customer?->name ?? fake()->name(),
                    'status' => $statuses[array_rand($statuses)],
                    'notes' => fake()->boolean(25) ? fake()->sentence() : null,
                    'subtotal' => 0,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'ordered_at' => $orderDate,
                ]
            );
        }
    }
}
