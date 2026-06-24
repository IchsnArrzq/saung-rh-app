<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\ServiceLog;
use App\Models\Table;
use App\Models\Tip;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Database\Seeder;

class Fase3DemoSeeder extends Seeder
{
    /**
     * Seed visitor logs, tips and service logs so the waiter/receptionist
     * portals have realistic data to display.
     */
    public function run(): void
    {
        $tables = Table::query()->pluck('id')->all();
        $waiter = User::query()->where('email', 'waiter@example.com')->first()
            ?? User::query()->first();

        if ($tables === [] || ! $waiter) {
            return;
        }

        // Visitor logs across the last 7 days.
        foreach (range(6, 0) as $offset) {
            $entries = random_int(4, 12);

            for ($i = 0; $i < $entries; $i++) {
                $source = fake()->randomElement(['qr', 'qr', 'walk_in', 'reservation']);
                $when = now()->subDays($offset)
                    ->setTime(random_int(10, 21), random_int(0, 59));

                VisitorLog::query()->create([
                    'table_id' => fake()->randomElement($tables),
                    'recorded_by' => $waiter->id,
                    'source' => $source,
                    'pax' => random_int(1, 6),
                    'visited_at' => $when,
                ]);
            }
        }

        // Tips received today + a few earlier.
        $orderIds = Order::query()->pluck('id')->all();

        foreach (range(0, 9) as $n) {
            Tip::query()->create([
                'waiter_id' => $waiter->id,
                'table_id' => fake()->randomElement($tables),
                'order_id' => $orderIds !== [] && fake()->boolean(60) ? fake()->randomElement($orderIds) : null,
                'amount' => fake()->randomElement([5000, 10000, 15000, 20000, 25000, 50000]),
                'note' => fake()->boolean(30) ? fake()->sentence(4) : null,
                'received_at' => now()->subDays(random_int(0, 3))->setTime(random_int(11, 21), random_int(0, 59)),
            ]);
        }

        // Service logs.
        $types = array_keys(ServiceLog::TYPES);

        foreach (range(0, 11) as $n) {
            ServiceLog::query()->create([
                'waiter_id' => $waiter->id,
                'table_id' => fake()->randomElement($tables),
                'type' => fake()->randomElement($types),
                'description' => fake()->boolean(50) ? fake()->sentence(6) : null,
                'served_at' => now()->subDays(random_int(0, 2))->setTime(random_int(11, 21), random_int(0, 59)),
            ]);
        }
    }
}
