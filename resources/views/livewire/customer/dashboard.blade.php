<div>
    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-2xl border border-base-300 bg-base-100 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Active Booking</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['active_booking'] }}</p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-100 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Total Booking</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['total_booking'] }}</p>
        </article>
        <article class="rounded-2xl border border-base-300 bg-base-100 p-5">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-secondary">Menu Dipesan</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['total_item_upcoming'] }}</p>
        </article>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-[1.25fr_1fr]">
        <article class="rounded-2xl border border-base-300 bg-base-100 p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Booking Mendatang</h2>
                <a href="{{ route('customer.bookings.create') }}" wire:navigate class="btn btn-sm btn-primary">
                    Booking Meja
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($upcomingReservations as $reservation)
                    <div class="rounded-xl border border-base-300 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold">{{ $reservation->table->code ?? '-' }} - {{ $reservation->pax }} orang</p>
                            <span class="badge badge-outline">{{ $reservation->status }}</span>
                        </div>
                        <p class="mt-1 text-sm text-base-content/70">{{ $reservation->reservation_at?->format('d M Y, H:i') }}</p>
                        <p class="mt-2 text-sm">
                            {{ $reservation->items->sum('qty') }} item, Estimasi Rp {{ number_format((float) $reservation->items->sum('line_total'), 0, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-base-300 p-4 text-sm text-base-content/60">
                        Belum ada booking mendatang.
                    </p>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl border border-base-300 bg-base-100 p-5">
            <h2 class="text-lg font-semibold">Riwayat Booking</h2>
            <div class="mt-4 space-y-3">
                @forelse ($reservationHistory as $reservation)
                    <div class="rounded-xl border border-base-300 p-3">
                        <p class="font-semibold">{{ $reservation->table->code ?? '-' }} &middot; {{ $reservation->pax }} orang</p>
                        <p class="text-xs text-base-content/60">{{ $reservation->reservation_at?->format('d M Y, H:i') }}</p>
                        <p class="mt-1 text-sm">Status: {{ $reservation->status }}</p>
                    </div>
                @empty
                    <p class="text-sm text-base-content/60">Belum ada riwayat booking.</p>
                @endforelse
            </div>
        </article>
    </section>
</div>
