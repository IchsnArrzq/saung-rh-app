<div>
    <section class="grid gap-4 md:grid-cols-3">
        <x-stat-card title="Booking Aktif" :value="$stats['active_booking']" icon="ri-calendar-check-line" color="primary" />
        <x-stat-card title="Total Booking" :value="$stats['total_booking']" icon="ri-calendar-2-line" />
        <x-stat-card title="Menu Dipesan" :value="$stats['total_item_upcoming']" icon="ri-bowl-line" />
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-[1.25fr_1fr]">
        <x-card title="Booking Mendatang">
            <x-slot:actions>
                <x-button variant="primary" size="sm" icon="ri-add-line"
                    :href="route('customer.bookings.create')" wire:navigate>
                    Booking Meja
                </x-button>
            </x-slot:actions>

            <div class="space-y-3">
                @forelse ($upcomingReservations as $reservation)
                    <div class="rounded-xl border border-base-300 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold">{{ $reservation->table->code ?? '-' }} - {{ $reservation->pax }} orang</p>
                            <x-status-badge :status="$reservation->status"
                                :enum="\App\Domains\Reservation\Enums\ReservationStatus::class" />
                        </div>
                        <p class="mt-1 text-sm text-base-content/70">{{ $reservation->reservation_at?->format('d M Y, H:i') }}</p>
                        <p class="mt-2 text-sm">
                            {{ $reservation->items->sum('qty') }} item, Estimasi Rp {{ number_format((float) $reservation->items->sum('line_total'), 0, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <x-empty-state icon="ri-calendar-line" title="Belum ada booking mendatang"
                        description="Pesan meja lebih awal supaya tempat Anda aman.">
                        <x-slot:actions>
                            <x-button variant="primary" size="sm" icon="ri-add-line"
                                :href="route('customer.bookings.create')" wire:navigate>
                                Booking Meja
                            </x-button>
                        </x-slot:actions>
                    </x-empty-state>
                @endforelse
            </div>
        </x-card>

        <x-card title="Riwayat Booking">
            <div class="space-y-3">
                @forelse ($reservationHistory as $reservation)
                    <div class="rounded-xl border border-base-300 p-3">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold">{{ $reservation->table->code ?? '-' }} &middot; {{ $reservation->pax }} orang</p>
                                <p class="text-xs text-base-content/60">{{ $reservation->reservation_at?->format('d M Y, H:i') }}</p>
                            </div>
                            <x-status-badge :status="$reservation->status"
                                :enum="\App\Domains\Reservation\Enums\ReservationStatus::class" size="sm" />
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="ri-history-line" title="Belum ada riwayat booking" />
                @endforelse
            </div>
        </x-card>
    </section>
</div>
