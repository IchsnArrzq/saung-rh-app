<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-md">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari no order, pelanggan, status, meja...">
                </div>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('orders.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Buat Order
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>No Order</th>
                    <th>Meja</th>
                    <th>Status</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr wire:key="order-{{ $order->id }}">
                        <td>
                            <p class="font-semibold">{{ $order->order_number }}</p>
                            <p class="text-xs text-stone-500">{{ $order->ordered_at?->format('d M Y H:i') }}</p>
                        </td>
                        <td>{{ $order->table->code ?? '-' }}</td>
                        <td>
                            <span class="badge badge-outline">{{ str_replace('_', ' ', $order->status) }}</span>
                        </td>
                        <td>{{ $order->items_count }}</td>
                        <td>
                            <p class="font-semibold">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</p>
                            <p class="text-xs text-stone-500">Paid Rp {{ number_format((float) ($order->paid_total ?? 0), 0, ',', '.') }}</p>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex flex-wrap justify-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline"
                                    wire:click="showDetail('{{ $order->id }}')">
                                    Detail
                                </button>
                                <button type="button" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700"
                                    data-confirm="Buat payment cash untuk sisa tagihan order ini?"
                                    data-confirm-title="Buat Payment"
                                    data-confirm-yes="Ya, Buat"
                                    wire:click="createPayment('{{ $order->id }}')">
                                    Payment
                                </button>
                                <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    data-confirm="Hapus order ini?"
                                    wire:click="delete('{{ $order->id }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-stone-500">Belum ada data order.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $orders->links() }}</div>

    <x-modal name="order-detail-modal" maxWidth="4xl">
        @if ($selectedOrder)
            <div class="space-y-5">
                <div class="flex items-start justify-between gap-3 print:hidden">
                    <div>
                        <h3 class="text-xl font-semibold text-stone-900">Detail Order</h3>
                        <p class="mt-1 text-sm text-stone-500">{{ $selectedOrder['order_number'] }} - {{ $selectedOrder['ordered_at'] }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline"
                            onclick="
                                const content = document.getElementById('order-receipt-print')?.innerHTML || '';
                                const popup = window.open('', '_blank', 'width=420,height=720');
                                popup.document.write('<html><head><title>Struk {{ $selectedOrder['order_number'] }}</title><style>body{font-family:Arial,sans-serif;font-size:12px;padding:16px}.center{text-align:center}.row{display:flex;justify-content:space-between;gap:12px}table{width:100%;border-collapse:collapse}td,th{padding:4px 0;border-bottom:1px dashed #ddd;text-align:left}.total{font-weight:700;font-size:14px}</style></head><body>'+content+'</body></html>');
                                popup.document.close();
                                popup.focus();
                                popup.print();
                            ">
                            <i class="ri-printer-line"></i>
                            Cetak Struk
                        </button>
                        <button type="button" class="btn btn-sm btn-ghost btn-circle" x-on:click="$dispatch('close')">
                            <i class="ri-close-line text-lg"></i>
                        </button>
                    </div>
                </div>

                <div id="order-receipt-print" class="rounded-2xl border border-stone-200 bg-white p-4">
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
                        <div class="row"><span>Paid</span><span>Rp {{ number_format((float) $selectedOrder['paid_total'], 0, ',', '.') }}</span></div>
                        <div class="row"><span>Sisa</span><span>Rp {{ number_format((float) $selectedOrder['remaining_total'], 0, ',', '.') }}</span></div>
                    </div>

                    @if ($selectedOrder['notes'] !== '')
                        <p style="margin-top:12px;">Catatan: {{ $selectedOrder['notes'] }}</p>
                    @endif
                </div>

                <div class="overflow-x-auto rounded-2xl border border-stone-200 print:hidden">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Jumlah</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($selectedOrder['payments'] as $payment)
                                <tr>
                                    <td>{{ $payment['method'] }}<br><span class="text-xs text-stone-500">{{ $payment['reference'] }}</span></td>
                                    <td><span class="badge badge-outline">{{ $payment['status'] }}</span></td>
                                    <td>Rp {{ number_format((float) $payment['amount'], 0, ',', '.') }}</td>
                                    <td>{{ $payment['paid_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-stone-500">Belum ada payment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-modal>
</div>
