<div wire:key="order-status" wire:poll.10s>
    <div class="mb-2 flex items-center justify-between">
        <span class="text-sm font-semibold"><i class="ri-timer-flash-line text-primary"></i> Status Antrian Pesanan</span>
        @if ($queueTotal > 0)
            <span class="badge badge-ghost badge-sm">{{ $queueTotal }} order di dapur</span>
        @endif
    </div>

    @if (! $tableId)
        <div class="alert alert-info text-sm">
            <i class="ri-qr-scan-2-line"></i>
            <span>Scan QR meja Anda untuk memantau status pesanan.</span>
        </div>
    @elseif ($orders->isEmpty())
        <div class="rounded-xl border border-dashed border-base-300 bg-base-200/40 p-6 text-center">
            <i class="ri-restaurant-2-line mb-2 text-3xl text-base-content/30"></i>
            <p class="text-sm text-base-content/60">Belum ada pesanan aktif. Yuk pesan menu favorit Anda!</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                @php
                    // Guest-facing wording, deliberately warmer than the Enum's
                    // operational labels ("Disiapkan"/"Siap") shown to staff.
                    [$label, $badge, $icon] = match ($order->status) {
                        \App\Domains\Order\Enums\OrderStatus::Preparing => ['Sedang dimasak', 'badge-info', 'ri-fire-line'],
                        \App\Domains\Order\Enums\OrderStatus::Ready => ['Siap diantar', 'badge-success', 'ri-checkbox-circle-line'],
                        default => ['Menunggu dapur', 'badge-warning', 'ri-time-line'],
                    };
                    $itemCount = $order->items->sum('qty');
                @endphp
                <article class="rounded-xl border border-base-300 bg-base-100 p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold">#{{ $order->order_number }}</p>
                            <p class="text-xs text-base-content/60">
                                {{ $itemCount }} item &middot; {{ $order->ordered_at->format('H:i') }}
                            </p>
                        </div>
                        <span class="badge {{ $badge }} badge-sm gap-1 font-semibold">
                            <i class="{{ $icon }}"></i> {{ $label }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-2">
                        @if ($order->status === \App\Domains\Order\Enums\OrderStatus::Ready)
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-success">
                                <i class="ri-walk-line"></i> Pesanan sedang diantar ke meja
                            </span>
                        @elseif ($order->queue_position)
                            <span class="inline-flex items-center gap-1 text-xs">
                                <i class="ri-hashtag text-base-content/50"></i>
                                Antrian ke-<span class="font-bold text-primary">{{ $order->queue_position }}</span>
                                <span class="text-base-content/50">dari {{ $queueTotal }}</span>
                            </span>
                        @else
                            <span class="text-xs text-base-content/50">Diproses</span>
                        @endif

                        <div x-data="{ start: '{{ $order->ordered_at->toIso8601String() }}', elapsed: '0m 0s' }"
                            x-init="setInterval(() => {
                                let diff = Math.floor((new Date() - new Date(start)) / 1000);
                                if (diff < 0) diff = 0;
                                let h = Math.floor(diff / 3600);
                                let m = Math.floor((diff % 3600) / 60);
                                let s = diff % 60;
                                elapsed = h > 0 ? h + 'j ' + m + 'm' : m + 'm ' + s + 's';
                            }, 1000)"
                            class="inline-flex items-center gap-1 rounded-lg bg-base-200 px-2 py-1 text-xs font-medium">
                            <i class="ri-time-line text-base-content/50"></i>
                            <span x-text="elapsed"></span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
