<div class="space-y-5">
    @include('admin.partials.flash')

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari metode, status, referensi, no order..." label="Cari pembayaran" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            @can('create', App\Models\Payment::class)
                <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('payments.create')">
                    Tambah Pembayaran
                </x-button>
            @endcan
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Order</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Jumlah</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($payments as $payment)
            <tr wire:key="payment-{{ $payment->id }}">
                <td>{{ $payment->order->order_number ?? '-' }}</td>
                <td class="capitalize">{{ str_replace('_', ' ', $payment->method) }}</td>
                <td>
                    <x-status-badge :status="$payment->status"
                        :enum="\App\Domains\Payment\Enums\PaymentStatus::class" />
                </td>
                <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        @can('update', $payment)
                            <x-button variant="warning" size="sm" :href="route('payments.edit', $payment)">
                                Edit
                            </x-button>
                        @endcan

                        @can('delete', $payment)
                            <x-button variant="error" size="sm" class="text-white"
                                data-confirm="Hapus pembayaran ini?"
                                wire:click="delete('{{ $payment->id }}')"
                                loading="delete('{{ $payment->id }}')">
                                Hapus
                            </x-button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-base-content/50">Belum ada data pembayaran.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $payments->links() }}</div>
</div>
