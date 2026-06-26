<div class="space-y-5">
    @php
        $methodLabels = [
            'cash' => 'Tunai', 'qris' => 'QRIS', 'debit_card' => 'Kartu Debit',
            'credit_card' => 'Kartu Kredit', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet',
        ];
    @endphp

    @if (session('success'))
        <div class="rounded-xl border border-success/30 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Tagihan Meja</h1>
                <p class="text-sm text-base-content/70">Tutup tagihan pesanan dine-in (QR / pelanggan / waiter) yang belum lunas.</p>
            </div>
            <div class="rounded-xl border border-warning/30 bg-warning/10 px-4 py-2 text-right">
                <p class="text-xs text-base-content/60">Total Belum Lunas</p>
                <p class="text-lg font-bold text-warning">Rp {{ number_format((float) $totalOutstanding, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="relative mt-4 w-full max-w-md">
            <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
            <input type="text" class="input input-bordered w-full pl-10"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari no. order, nama, atau kode meja...">
        </div>
    </div>

    {{-- Bills --}}
    @if ($bills->isEmpty())
        <div class="rounded-2xl border border-dashed border-base-300 bg-base-100 p-10 text-center">
            <i class="ri-checkbox-circle-line text-4xl text-success"></i>
            <p class="mt-2 font-medium">Tidak ada tagihan terbuka.</p>
            <p class="text-sm text-base-content/60">Semua pesanan sudah lunas.</p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($bills as $bill)
                <article class="flex flex-col rounded-2xl border border-base-300 bg-base-100 p-4">
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
                            <span class="badge badge-ghost badge-sm">{{ $bill['source'] }}</span>
                            <span class="badge badge-outline badge-sm">{{ $bill['status'] }}</span>
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

                    <button type="button" wire:click="openSettle('{{ $bill['id'] }}')"
                        class="btn btn-primary btn-sm mt-4 w-full">
                        <i class="ri-cash-line"></i> Tutup Tagihan
                    </button>
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
                    <button type="button" wire:click="closeSettle" class="btn btn-sm btn-ghost btn-circle" aria-label="Tutup">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>

                <div class="rounded-xl border border-base-300 bg-base-200/50 p-4 text-center">
                    <p class="text-xs text-base-content/60">Jumlah yang harus dibayar</p>
                    <p class="text-3xl font-bold text-primary">Rp {{ number_format($payBill['outstanding'], 0, ',', '.') }}</p>
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium">Metode Pembayaran</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($methods as $m)
                            <button type="button" wire:click="$set('method', '{{ $m }}')"
                                class="btn btn-sm {{ $method === $m ? 'btn-primary' : 'btn-ghost border border-base-300' }}">
                                {{ $methodLabels[$m] ?? $m }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @error('settle')<p class="text-sm text-error">{{ $message }}</p>@enderror

                <button type="button" wire:click="settle" wire:confirm="Konfirmasi pembayaran tagihan ini?"
                    class="btn btn-primary w-full">
                    <i class="ri-checkbox-circle-line"></i> Konfirmasi Pembayaran
                </button>
            </div>
        @endif
    </x-modal>
</div>
