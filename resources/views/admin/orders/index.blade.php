<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Order</h2>
            <a href="{{ route('orders.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Buat Order
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

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
                    <tr>
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
                                <a href="{{ route('orders.edit', $order) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <form action="{{ route('orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Hapus order ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
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

    <div class="mt-4">{{ $orders->links() }}</div>
</x-app-layout>
