<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari no order, pelanggan, status, meja..." label="Cari order" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('orders.create')">
                Buat Order
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>No Order</th>
                <th>Meja</th>
                <th>Status</th>
                <th>Item</th>
                <th>Total</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($orders as $order)
            <tr wire:key="order-{{ $order->id }}">
                <td>
                    <p class="font-semibold">{{ $order->order_number }}</p>
                    <p class="text-xs text-base-content/60">{{ $order->ordered_at?->format('d M Y H:i') }}</p>
                </td>
                <td>{{ $order->table->code ?? '-' }}</td>
                <td>
                    <x-status-badge :status="$order->status" />
                </td>
                <td>{{ $order->items_count }}</td>
                <td>
                    <p class="font-semibold">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</p>
                    <p class="text-xs text-base-content/60">Dibayar Rp {{ number_format((float) ($order->paid_total ?? 0), 0, ',', '.') }}</p>
                </td>
                <td class="text-right">
                    <div class="inline-flex flex-wrap justify-end gap-2">
                        <x-button variant="outline" size="sm" wire:click="showDetail('{{ $order->id }}')">
                            Detail
                        </x-button>
                        <x-button variant="accent" size="sm"
                            data-confirm="Buat payment cash untuk sisa tagihan order ini?"
                            data-confirm-title="Buat Payment"
                            data-confirm-yes="Ya, Buat"
                            wire:click="createPayment('{{ $order->id }}')"
                            loading="createPayment('{{ $order->id }}')">
                            Payment
                        </x-button>
                        <x-button variant="warning" size="sm" :href="route('orders.edit', $order)">Edit</x-button>
                        <x-button variant="error" size="sm" class="text-white"
                            data-confirm="Hapus order ini?"
                            wire:click="delete('{{ $order->id }}')"
                            loading="delete('{{ $order->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-base-content/50">Belum ada data order.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $orders->links() }}</div>

    <x-modal name="order-detail-modal" maxWidth="4xl">
        @if ($selectedOrder)
            <div class="space-y-5">
                <div class="flex items-start justify-between gap-3 print:hidden">
                    <div>
                        <h3 class="text-xl font-semibold">Detail Order</h3>
                        <p class="mt-1 text-sm text-base-content/60">{{ $selectedOrder['order_number'] }} - {{ $selectedOrder['ordered_at'] }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-button variant="outline" size="sm" icon="ri-printer-line"
                            onclick="
                                const content = document.getElementById('order-receipt-print')?.innerHTML || '';
                                const popup = window.open('', '_blank', 'width=420,height=720');
                                popup.document.write('<html><head><title>Struk {{ $selectedOrder['order_number'] }}</title><style>body{font-family:Arial,sans-serif;font-size:12px;padding:16px}.center{text-align:center}.row{display:flex;justify-content:space-between;gap:12px}table{width:100%;border-collapse:collapse}td,th{padding:4px 0;border-bottom:1px dashed #ddd;text-align:left}.total{font-weight:700;font-size:14px}</style></head><body>'+content+'</body></html>');
                                popup.document.close();
                                popup.focus();
                                popup.print();
                            ">
                            Cetak Struk
                        </x-button>
                        <x-button variant="ghost" size="sm" shape="circle" icon="ri-close-line text-lg"
                            label="Tutup" x-on:click="$dispatch('close')" />
                    </div>
                </div>

                <div id="order-receipt-print" class="rounded-xl border border-base-300 bg-base-100 p-4">
                    <div class="center">
                        <h2 style="margin:0;font-size:18px;">SaungRH</h2>
                        <p style="margin:4px 0 12px;">Struk Order</p>
                    </div>

                    <div class="row"><span>No Order</span><strong>{{ $selectedOrder['order_number'] }}</strong></div>
                    <div class="row"><span>Tanggal</span><span>{{ $selectedOrder['ordered_at'] }}</span></div>
                    <div class="row"><span>Customer</span><span>{{ $selectedOrder['customer_name'] }}</span></div>
                    <div class="row"><span>Meja</span><span>{{ $selectedOrder['table'] }}</span></div>
                    <div class="row"><span>Kasir</span><span>{{ $selectedOrder['cashier'] }}</span></div>

                    <table style="margin-top:12px;">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($selectedOrder['items'] as $item)
                                <tr>
                                    <td>
                                        {{ $item['name'] }}
                                        @if ($item['notes'] !== '')
                                            <br><small>{{ $item['notes'] }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item['qty'] }}</td>
                                    <td>Rp {{ number_format((float) $item['line_total'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div style="margin-top:12px;">
                        <div class="row"><span>Subtotal</span><span>Rp {{ number_format((float) $selectedOrder['subtotal'], 0, ',', '.') }}</span></div>
                        <div class="row"><span>Diskon</span><span>Rp {{ number_format((float) $selectedOrder['discount'], 0, ',', '.') }}</span></div>
                        <div class="row"><span>Pajak</span><span>Rp {{ number_format((float) $selectedOrder['tax'], 0, ',', '.') }}</span></div>
                        <div class="row total"><span>Total</span><span>Rp {{ number_format((float) $selectedOrder['total'], 0, ',', '.') }}</span></div>
                        <div class="row"><span>Dibayar</span><span>Rp {{ number_format((float) $selectedOrder['paid_total'], 0, ',', '.') }}</span></div>
                        <div class="row"><span>Sisa</span><span>Rp {{ number_format((float) $selectedOrder['remaining_total'], 0, ',', '.') }}</span></div>
                    </div>

                    @if ($selectedOrder['notes'] !== '')
                        <p style="margin-top:12px;">Catatan: {{ $selectedOrder['notes'] }}</p>
                    @endif
                </div>

                <x-data-table class="print:hidden" :zebra="false">
                    <x-slot:head>
                        <tr>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Jumlah</th>
                            <th>Waktu</th>
                        </tr>
                    </x-slot:head>

                    @forelse ($selectedOrder['payments'] as $payment)
                        <tr>
                            <td>{{ $payment['method'] }}<br><span class="text-xs text-base-content/60">{{ $payment['reference'] }}</span></td>
                            <td>
                                <x-status-badge :status="$payment['status']"
                                    :enum="\App\Domains\Payment\Enums\PaymentStatus::class" />
                            </td>
                            <td>Rp {{ number_format((float) $payment['amount'], 0, ',', '.') }}</td>
                            <td>{{ $payment['paid_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-base-content/50">Belum ada payment.</td>
                        </tr>
                    @endforelse
                </x-data-table>
            </div>
        @endif
    </x-modal>
</div>
