@php
    $toneClasses = [
        'primary' => 'bg-primary/15 text-primary',
        'success' => 'bg-success/15 text-success',
        'warning' => 'bg-warning/15 text-warning',
        'info' => 'bg-info/15 text-info',
    ];
@endphp

<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold">Dashboard Admin</h2>
                <p class="mt-1 text-sm text-secondary">Ringkasan operasional restoran hari ini.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($summary['shortcuts'] as $shortcut)
                    <a href="{{ $shortcut['url'] }}" class="btn btn-sm btn-primary">
                        <i class="{{ $shortcut['icon'] }}"></i>
                        {{ $shortcut['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">
        @if (count($summary['alerts']) > 0)
            <section class="grid gap-3 lg:grid-cols-3">
                @foreach ($summary['alerts'] as $alert)
                    <div class="alert {{ $alert['class'] }} rounded-xl">
                        <i class="{{ $alert['icon'] }} text-lg"></i>
                        <span class="text-sm font-medium">{{ $alert['label'] }}</span>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($summary['metrics'] as $metric)
                <article class="card bg-base-100 shadow-sm">
                    <div class="card-body gap-4 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-secondary">
                                    {{ $metric['label'] }}
                                </p>
                                <p class="mt-2 text-2xl font-bold text-base-content">{{ $metric['value'] }}</p>
                            </div>
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl {{ $toneClasses[$metric['tone']] ?? $toneClasses['primary'] }}">
                                <i class="{{ $metric['icon'] }} text-xl"></i>
                            </span>
                        </div>
                        <p class="text-sm text-secondary">{{ $metric['caption'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-5 xl:grid-cols-12">
            <article class="card bg-base-100 shadow-sm xl:col-span-8">
                <div class="card-body p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-base-content">Pendapatan 7 Hari</h3>
                            <p class="text-sm text-secondary">Berdasarkan pembayaran berstatus paid.</p>
                        </div>
                        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-ghost">
                            <i class="ri-arrow-right-line"></i>
                            Detail
                        </a>
                    </div>

                    <div wire:ignore class="mt-2 min-h-[300px]">
                        <div
                            x-data="salesChartHandler(@js($summary['sales_chart']['labels']), @js($summary['sales_chart']['values']))"
                            x-ref="apexChart"
                            class="min-h-[300px] w-full">
                        </div>
                    </div>
                </div>
            </article>

            <article class="card bg-base-100 shadow-sm xl:col-span-4">
                <div class="card-body p-5">
                    <h3 class="text-lg font-semibold text-base-content">Status Order</h3>
                    <div class="mt-2 space-y-3">
                        @foreach ($summary['order_statuses'] as $status)
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-medium text-base-content">{{ $status['label'] }}</span>
                                <span class="badge {{ $status['class'] }} badge-lg">{{ $status['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-5 lg:grid-cols-3">
            <article class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <h3 class="text-lg font-semibold text-base-content">Status Meja</h3>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        @foreach ($summary['table_statuses'] as $tableStatus)
                            <div class="rounded-xl bg-base-200 p-4">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg {{ $toneClasses[$tableStatus['tone']] ?? $toneClasses['primary'] }}">
                                    <i class="{{ $tableStatus['icon'] }}"></i>
                                </span>
                                <p class="mt-3 text-2xl font-bold text-base-content">{{ $tableStatus['value'] }}</p>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-secondary">
                                    {{ $tableStatus['label'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <h3 class="text-lg font-semibold text-base-content">Menu Terlaris Hari Ini</h3>
                    <div class="mt-2 space-y-3">
                        @forelse ($summary['top_menus'] as $menu)
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-base-content">{{ $menu->menu_name_snapshot }}</p>
                                    <p class="text-xs text-secondary">Rp {{ number_format((float) $menu->total_revenue, 0, ',', '.') }}</p>
                                </div>
                                <span class="badge badge-primary badge-outline">{{ (int) $menu->total_qty }} terjual</span>
                            </div>
                        @empty
                            <p class="rounded-xl bg-base-200 p-4 text-sm text-secondary">Belum ada penjualan menu hari ini.</p>
                        @endforelse
                    </div>
                </div>
            </article>

            <article class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <h3 class="text-lg font-semibold text-base-content">Metode Pembayaran</h3>
                    <div class="mt-2 space-y-3">
                        @forelse ($summary['payment_methods'] as $payment)
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold capitalize text-base-content">{{ str_replace('_', ' ', $payment->method) }}</p>
                                    <p class="text-xs text-secondary">{{ (int) $payment->total_count }} transaksi</p>
                                </div>
                                <p class="font-semibold text-base-content">Rp {{ number_format((float) $payment->total_amount, 0, ',', '.') }}</p>
                            </div>
                        @empty
                            <p class="rounded-xl bg-base-200 p-4 text-sm text-secondary">Belum ada pembayaran paid hari ini.</p>
                        @endforelse
                    </div>
                </div>
            </article>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            <article class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-base-content">Order Terbaru</h3>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-ghost">Lihat Semua</a>
                    </div>
                    <div class="mt-2 overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Meja</th>
                                    <th>Status</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($summary['recent_orders'] as $order)
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-base-content">{{ $order->order_number ?? '-' }}</p>
                                            <p class="text-xs text-secondary">{{ $order->ordered_at?->format('d M Y H:i') ?? '-' }}</p>
                                        </td>
                                        <td>{{ $order->table?->code ?? '-' }}</td>
                                        <td><span class="badge badge-ghost capitalize">{{ $order->status }}</span></td>
                                        <td class="text-right font-semibold">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-secondary">Belum ada order.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>

            <article class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-base-content">Reservasi Hari Ini</h3>
                        <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-ghost">Lihat Semua</a>
                    </div>
                    <div class="mt-2 space-y-3">
                        @forelse ($summary['today_reservations'] as $reservation)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-base-200 p-4">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-base-content">{{ $reservation->customer_name }}</p>
                                    <p class="text-sm text-secondary">
                                        {{ $reservation->reservation_at?->format('H:i') ?? '-' }}
                                        <span class="mx-1">-</span>
                                        {{ $reservation->table?->code ?? 'Tanpa meja' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-base-content">{{ $reservation->pax }} pax</p>
                                    <span class="badge badge-outline capitalize">{{ $reservation->status }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-xl bg-base-200 p-4 text-sm text-secondary">Tidak ada reservasi hari ini.</p>
                        @endforelse
                    </div>
                </div>
            </article>
        </section>
    </div>
</x-admin-layout>
