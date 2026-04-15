<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Reservation::query()->count() >= 20) {
            return;
        }

        $tableIds = Table::query()->pluck('id')->all();
        $customers = User::query()->role('customer')->get();
        $statuses = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];

        if ($customers->isEmpty() || empty($tableIds)) {
            return;
        }

        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers->random();

            Reservation::query()->create([
                'user_id' => $customer->id,
                'table_id' => $tableIds[array_rand($tableIds)],
                'customer_name' => $customer->name,
                'phone' => fake()->phoneNumber(),
                'pax' => rand(1, 8),
                'reservation_at' => now()->addDays(rand(-7, 14))->setTime(rand(10, 20), rand(0, 1) * 30),
                'status' => $statuses[array_rand($statuses)],
                'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            ]);
        }
    }
}
