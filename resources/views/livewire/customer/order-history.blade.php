<div>
    <x-page-header title="Riwayat pesanan"
        description="Semua pesanan yang Anda buat dari akun ini, terbaru lebih dulu." />

    {{-- Jumlah diambil dari total() paginator, bukan dari baris yang sedang
         dibuka — halaman ini cuma memuat sepuluh baris sekaligus. --}}
    @if ($orders->total() > 0)
        <p class="mt-4 text-sm text-base-content/70">
            <span class="font-semibold tabular-nums">{{ $orders->total() }}</span> pesanan tercatat.
        </p>
    @endif

    <div class="mt-4 space-y-4" wire:loading.remove wire:target="gotoPage,nextPage,previousPage">
        @forelse ($orders as $order)
            <article class="rounded-xl border border-base-300 bg-base-100 p-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold">{{ $order->order_number }}</p>
                        <p class="text-sm text-base-content/60">
                            {{ $order->ordered_at?->format('d M Y, H:i') ?? '-' }}
                            @if ($order->table?->code)
                                &middot; Meja {{ $order->table->code }}
                            @endif
                        </p>
                    </div>
                    <x-status-badge :status="$order->status" />
                </div>

                <ul class="mt-3 divide-y divide-base-300">
                    @foreach ($order->items as $item)
                        <li class="flex items-baseline justify-between gap-3 py-2 first:pt-0 last:pb-0">
                            <span class="text-sm">
                                <span class="tabular-nums">{{ $item->qty }}</span>×
                                {{ $item->menu_name_snapshot ?? 'Menu' }}
                            </span>
                            <span class="shrink-0 text-sm tabular-nums">
                                Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-3 flex items-baseline justify-between gap-3 border-t border-base-300 pt-3">
                    <span class="text-sm font-semibold">Total</span>
                    <span class="font-semibold tabular-nums">
                        Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                    </span>
                </div>
            </article>
        @empty
            <x-empty-state icon="ri-receipt-line" title="Belum ada pesanan"
                description="Pesanan yang Anda buat dari akun ini akan tercatat di sini.">
                <x-slot:actions>
                    <x-button variant="primary" icon="ri-restaurant-line"
                        :href="route('customer.menus.tables')" wire:navigate>
                        Mulai pesan
                    </x-button>
                </x-slot:actions>
            </x-empty-state>
        @endforelse
    </div>

    <div class="mt-4 space-y-4" wire:loading wire:target="gotoPage,nextPage,previousPage">
        <x-skeleton :rows="3" />
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
