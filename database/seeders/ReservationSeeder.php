<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\TableStatus;
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
            $status = $statuses[array_rand($statuses)];

            Reservation::query()->create(array_merge([
                'user_id' => $customer->id,
                'table_id' => $tableIds[array_rand($tableIds)],
                'customer_name' => $customer->name,
                'phone' => fake()->phoneNumber(),
                'pax' => rand(1, 8),
                'reservation_at' => now()->addDays(rand(-7, 14))->setTime(rand(10, 20), rand(0, 1) * 30),
                'status' => $status,
                'notes' => fake()->boolean(30) ? fake()->sentence() : null,
            ], $this->depositFields($status)));
        }

        $this->seedAutoReleaseDemo($tableIds, $customers);
    }

    /**
     * Deposit / hold attributes consistent with a reservation status.
     *
     * @return array<string, mixed>
     */
    private function depositFields(string $status): array
    {
        return match ($status) {
            'pending' => [
                // Still holding a table, waiting for the deposit within the window.
                'hold_until' => now()->addMinutes(rand(30, 120)),
            ],
            'confirmed', 'seated', 'completed' => [
                'deposit_amount' => 50000,
                'deposit_paid_at' => now()->subDays(rand(1, 5)),
            ],
            default => [],
        };
    }

    /**
     * Guaranteed candidates the auto-release command will act on, so the
     * scheduled job is demonstrable right after seeding.
     *
     * @param  array<int, string>  $tableIds
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $customers
     */
    private function seedAutoReleaseDemo(array $tableIds, $customers): void
    {
        // Expired hold: pending, deposit window lapsed, no deposit -> auto-cancel.
        Reservation::query()->create([
            'user_id' => $customers->random()->id,
            'table_id' => $tableIds[array_rand($tableIds)],
            'customer_name' => 'DEMO Hold Kadaluarsa',
            'phone' => '0800-0000-001',
            'pax' => 2,
            'reservation_at' => now()->addHours(3),
            'status' => 'pending',
            'hold_until' => now()->subMinutes(20),
        ]);

        // No-show: confirmed, reserved time passed beyond grace, never seated.
        // Lock its table so the auto-release visibly frees it.
        $reservedStatus = TableStatus::query()->where('key', Reservation::RESERVED_STATUS_KEY)->first();
        $lockedTable = Table::query()->create([
            'code' => 'RSV-DEMO',
            'name' => 'Meja Reservasi Demo',
            'capacity' => 4,
            'table_status_id' => $reservedStatus?->id,
        ]);

        Reservation::query()->create([
            'user_id' => $customers->random()->id,
            'table_id' => $lockedTable->id,
            'customer_name' => 'DEMO No-show',
            'phone' => '0800-0000-002',
            'pax' => 4,
            'reservation_at' => now()->subHour(),
            'status' => 'confirmed',
            'deposit_amount' => 50000,
            'deposit_paid_at' => now()->subDay(),
        ]);
    }
}
