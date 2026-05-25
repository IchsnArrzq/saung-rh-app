<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                        placeholder="Cari no order, pelanggan, status, meja...">
                </label>
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
                        <td>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    onclick="if (!confirm('Hapus order ini?')) return false;"
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
</div>

