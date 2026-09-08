<div class="space-y-6">
    <x-card class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex w-full flex-col items-start gap-4 xl:w-auto xl:flex-row xl:items-center">

            <div class="flex items-center gap-1 rounded-xl bg-base-200 p-1">
                @foreach (['today' => 'Hari ini', 'this_month' => 'Bulan ini', 'this_year' => 'Tahun ini'] as $key => $label)
                    <x-button size="sm" :variant="$filterType === $key ? 'primary' : 'ghost'"
                        wire:click="setFilter('{{ $key }}')">{{ $label }}</x-button>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                {{-- check-ui-allow: input date mengirim event `change` saat tanggal dipilih, bukan per ketukan — debounce hanya menunda hasilnya. --}}
                <x-input type="date" bare size="sm" label="Tanggal mulai" wire:model.live="startDate" />
                <span class="text-sm font-medium text-base-content/60">s/d</span>
                {{-- check-ui-allow: sama seperti tanggal mulai — event `change`, bukan ketukan. --}}
                <x-input type="date" bare size="sm" label="Tanggal akhir" wire:model.live="endDate" />
            </div>

            <div wire:loading wire:target="startDate, endDate, setFilter" class="text-sm font-medium text-accent">
                <span class="loading loading-spinner loading-sm align-middle" aria-label="Memuat laporan"></span>
            </div>
        </div>

        <div>
            <x-button variant="primary" size="sm" icon="ri-file-excel-2-line" wire:click="exportExcel"
                loading="exportExcel">
                Ekspor Excel
            </x-button>
        </div>
    </x-card>

    <div class="grid gap-6 lg:grid-cols-12">

        <div class="flex flex-col gap-4 lg:col-span-4">
            <x-stat-card class="flex-1" title="Total pendapatan" icon="ri-money-dollar-circle-line"
                :value="'Rp ' . number_format((float) $totalSales, 0, ',', '.')" />

            <x-stat-card class="flex-1" title="Pesanan masuk" icon="ri-shopping-bag-3-line"
                :value="number_format((float) $totalCustomers, 0, ',', '.')" description="Transaksi pada rentang ini" />
        </div>

        <x-card title="Grafik penjualan" class="flex flex-col lg:col-span-8">
            <div wire:ignore class="w-full flex-1">
                <div x-data="salesChartHandler(@js($chartLabels), @js($chartValues))"
                     @chart-updated.window="updateChart($event.detail.data.labels, $event.detail.data.values)"
                     x-ref="apexChart"
                     class="min-h-72 w-full">
                </div>
            </div>
        </x-card>

    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <x-card title="5 menu terlaris">
            <div class="space-y-4">
                @forelse ($bestSellingMenus as $item)
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-base-200 font-semibold tabular-nums text-base-content/70">
                                {{ $loop->iteration }}
                            </div>
                            <p class="font-medium">{{ $item->menu_name_snapshot }}</p>
                        </div>
                        <x-badge color="primary" outline class="tabular-nums">{{ $item->total_qty }} terjual</x-badge>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-base-content/60">
                        Belum ada menu terjual pada rentang tanggal ini. Coba perlebar rentangnya.
                    </p>
                @endforelse
            </div>
        </x-card>

        <x-card title="Pendapatan per kasir">
            <div class="space-y-4">
                @forelse ($revenuePerCashier as $cashier)
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-accent/10 text-accent">
                                <i class="ri-user-smile-line text-lg" aria-hidden="true"></i>
                            </div>
                            <div>
                                <p class="font-medium">{{ $cashier->name }}</p>
                                <p class="text-xs tabular-nums text-base-content/60">{{ $cashier->total_orders }} transaksi</p>
                            </div>
                        </div>
                        <p class="font-semibold tabular-nums">Rp {{ number_format((float) $cashier->total_revenue, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-base-content/60">
                        Belum ada transaksi terbayar pada rentang tanggal ini.
                    </p>
                @endforelse
            </div>
        </x-card>
    </div>
</div>
