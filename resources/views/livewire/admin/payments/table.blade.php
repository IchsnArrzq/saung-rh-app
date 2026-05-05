<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                        placeholder="Cari metode, status, referensi, no order...">
                </label>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('payments.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Pembayaran
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Jumlah</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr wire:key="payment-{{ $payment->id }}">
                        <td>{{ $payment->order->order_number ?? '-' }}</td>
                        <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                        <td>
                            <span class="badge badge-outline">{{ $payment->status }}</span>
                        </td>
                        <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <button type="button" class="btn btn-xs btn-error text-white"
                                    onclick="if (!confirm('Hapus pembayaran ini?')) return false;"
                                    wire:click="delete('{{ $payment->id }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-stone-500">Belum ada data pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $payments->links() }}</div>
</div>

