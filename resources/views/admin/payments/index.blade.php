<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Pembayaran</h2>
            <a href="{{ route('payments.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Pembayaran
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Metode</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Jumlah</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $payment->order->order_number ?? '-' }}</td>
                        <td>{{ str_replace('_', ' ', $payment->method) }}</td>
                        <td>{{ strtoupper($payment->type) }}</td>
                        <td>
                            <span class="badge badge-outline">{{ $payment->status }}</span>
                        </td>
                        <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <form action="{{ route('payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Hapus pembayaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-stone-500">Belum ada data pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</x-app-layout>
