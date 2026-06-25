<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\SpecialRequest;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class Fase6DemoSeeder extends Seeder
{
    /**
     * Seed staff shifts for this week and a spread of special requests so the
     * manager dashboard, approver and waiter handler all have content.
     */
    public function run(): void
    {
        $this->seedShifts();
        $this->seedSpecialRequests();
    }

    private function seedShifts(): void
    {
        if (Shift::query()->exists()) {
            return;
        }

        $staff = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['waiter', 'receptionist', 'chef', 'ob']))
            ->get();

        $monday = today()->startOfWeek();

        foreach ($staff as $i => $person) {
            // Two shifts each, spread across the week, including today.
            foreach ([0, 2] as $offset) {
                $day = $monday->copy()->addDays((($i + $offset) % 6));

                Shift::query()->create([
                    'user_id' => $person->id,
                    'shift_date' => $day->toDateString(),
                    'starts_at' => $i % 2 === 0 ? '09:00' : '14:00',
                    'ends_at' => $i % 2 === 0 ? '17:00' : '22:00',
                    'position' => $person->getRoleNames()->first(),
                    'status' => 'scheduled',
                ]);
            }

            // Guarantee at least one waiter is on shift today for matchmaking.
            if ($person->hasRole('waiter')) {
                Shift::query()->firstOrCreate([
                    'user_id' => $person->id,
                    'shift_date' => today()->toDateString(),
                ], [
                    'starts_at' => '09:00',
                    'ends_at' => '17:00',
                    'position' => 'waiter',
                    'status' => 'scheduled',
                ]);
            }
        }
    }

    private function seedSpecialRequests(): void
    {
        if (SpecialRequest::query()->exists()) {
            return;
        }

        $session = TableSession::query()->active()->with('table')->first();
        $waiter = User::query()->role('waiter')->first();
        $manager = User::query()->role('manager')->first();

        $samples = [
            ['category' => 'celebration', 'description' => 'Tolong siapkan kejutan kue ulang tahun jam 8 malam.', 'status' => 'pending'],
            ['category' => 'ambience', 'description' => 'Mohon kecilkan volume musik di area kami.', 'status' => 'pending'],
            ['category' => 'service', 'description' => 'Minta tambahan tisu dan air putih.', 'status' => 'assigned'],
            ['category' => 'kitchen', 'description' => 'Nasi goreng tanpa bawang, alergi.', 'status' => 'done'],
        ];

        foreach ($samples as $sample) {
            SpecialRequest::query()->create([
                'table_session_id' => $session?->id,
                'table_id' => $session?->table_id,
                'table_code' => $session?->table?->code ?? 'T-01',
                'requested_by' => 'Tamu Demo',
                'category' => $sample['category'],
                'description' => $sample['description'],
                'is_paid' => $sample['category'] === 'celebration',
                'price' => $sample['category'] === 'celebration' ? 150000 : null,
                'status' => $sample['status'],
                'approved_by' => in_array($sample['status'], ['assigned', 'done'], true) ? $manager?->id : null,
                'assigned_to' => in_array($sample['status'], ['assigned', 'done'], true) ? $waiter?->id : null,
                'handled_at' => $sample['status'] === 'done' ? now()->subHours(2) : null,
            ]);
        }
    }
}
