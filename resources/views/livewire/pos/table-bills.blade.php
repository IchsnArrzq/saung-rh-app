<div class="space-y-5">
    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    {{-- Header --}}
    <x-page-header title="Tagihan Meja"
        description="Tutup tagihan pesanan dine-in (QR / pelanggan / waiter) yang belum lunas.">
        <x-slot:actions>
            <div class="rounded-xl border border-warning/30 bg-warning/10 px-4 py-2 text-right">
                <p class="text-xs text-base-content/60">Total Belum Lunas</p>
                <p class="text-lg font-bold text-warning">Rp {{ number_format((float) $totalOutstanding, 0, ',', '.') }}</p>
            </div>
        </x-slot:actions>

        <x-search-input class="mt-4 max-w-md" wire:model.live.debounce.300ms="search"
            placeholder="Cari no. order, nama, atau kode meja..." label="Cari tagihan" />
    </x-page-header>

    {{-- Bills --}}
    @if ($bills->isEmpty())
        <x-empty-state icon="ri-checkbox-circle-line" title="Tidak ada tagihan terbuka"
            description="Semua pesanan sudah lunas." :dashed="true" />
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($bills as $bill)
                <article class="flex flex-col rounded-xl border border-base-300 bg-base-100 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold">
                                @if ($bill['table_code'])
                                    Meja {{ $bill['table_code'] }}
                                @else
                                    {{ $bill['customer_name'] ?: 'Tanpa Meja' }}
                                @endif
                            </p>
                            <p class="text-xs text-base-content/60">{{ $bill['order_number'] }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <x-badge color="ghost" size="sm">{{ $bill['source'] }}</x-badge>
                            <x-status-badge :status="$bill['status']" size="sm"
                                :enum="\App\Domains\Order\Enums\OrderStatus::class" />
                        </div>
                    </div>

                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach (array_slice($bill['items'], 0, 4) as $item)
                            <li class="flex justify-between gap-2 text-base-content/80">
                                <span class="truncate">{{ $item['qty'] }}× {{ $item['name'] }}</span>
                                <span class="shrink-0">Rp {{ number_format($item['line_total'], 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                        @if (count($bill['items']) > 4)
                            <li class="text-xs text-base-content/50">+{{ count($bill['items']) - 4 }} item lainnya</li>
                        @endif
                    </ul>

                    <div class="mt-3 border-t border-base-300 pt-3 text-sm">
                        @if ($bill['paid'] > 0)
                            <div class="flex justify-between text-base-content/60">
                                <span>Total</span>
                                <span>Rp {{ number_format($bill['total'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-success">
                                <span>Sudah dibayar</span>
                                <span>- Rp {{ number_format($bill['paid'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="mt-1 flex items-center justify-between font-semibold">
                            <span>Sisa Tagihan</span>
                            <span class="text-lg">Rp {{ number_format($bill['outstanding'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <x-button variant="primary" size="sm" :block="true" class="mt-4" icon="ri-cash-line"
                        wire:click="openSettle('{{ $bill['id'] }}')">
                        Tutup Tagihan
                    </x-button>
                </article>
            @endforeach
        </div>
    @endif

    {{-- Settle modal --}}
    <x-modal name="settle-bill-modal" maxWidth="md">
        @if ($payBill)
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold">Tutup Tagihan</h3>
                        <p class="text-sm text-base-content/60">
                            {{ $payBill['table_code'] ? 'Meja '.$payBill['table_code'] : ($payBill['customer_name'] ?: 'Tanpa Meja') }}
                            &middot; {{ $payBill['order_number'] }}
                        </p>
                    </div>
                    <x-button variant="ghost" size="sm" shape="circle" icon="ri-close-line text-lg"
                        label="Tutup" wire:click="closeSettle" />
                </div>

                <div class="rounded-xl border border-base-300 bg-base-200/50 p-4 text-center">
                    <p class="text-xs text-base-content/60">Jumlah yang harus dibayar</p>
                    <p class="text-3xl font-bold text-primary">Rp {{ number_format($payBill['outstanding'], 0, ',', '.') }}</p>
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium">Metode Pembayaran</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($methods as $m)
                            <x-button size="sm" wire:click="$set('method', '{{ $m->value }}')"
                                :variant="$method === $m->value ? 'primary' : 'ghost'"
                                class="{{ $method === $m->value ? '' : 'border border-base-300' }}">
                                {{ $m->label() }}
                            </x-button>
                        @endforeach
                    </div>
                </div>

                @error('settle')<p class="text-sm text-error">{{ $message }}</p>@enderror

                <x-button variant="primary" :block="true" icon="ri-checkbox-circle-line"
                    wire:click="settle" loading="settle"
                    data-confirm="Konfirmasi pembayaran tagihan ini?"
                    data-confirm-title="Konfirmasi Pembayaran" data-confirm-yes="Ya, Bayar">
                    Konfirmasi Pembayaran
                </x-button>
            </div>
        @endif
    </x-modal>
</div>
