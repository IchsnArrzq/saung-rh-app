<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-stone-200 shadow-sm">
        <div class="flex flex-col xl:flex-row items-start xl:items-center gap-4 w-full xl:w-auto">
            
            <div class="flex items-center rounded-xl border border-base-300 bg-base-200 p-1">
                <button type="button" wire:click="setFilter('today')" 
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all {{ $filterType === 'today' ? 'bg-primary text-primary-content shadow-sm' : 'text-secondary hover:text-base-content hover:bg-base-300' }}">
                    Hari Ini
                </button>
                <button type="button" wire:click="setFilter('this_month')" 
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all {{ $filterType === 'this_month' ? 'bg-primary text-primary-content shadow-sm' : 'text-secondary hover:text-base-content hover:bg-base-300' }}">
                    Bulan Ini
                </button>
                <button type="button" wire:click="setFilter('this_year')" 
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all {{ $filterType === 'this_year' ? 'bg-primary text-primary-content shadow-sm' : 'text-secondary hover:text-base-content hover:bg-base-300' }}">
                    Tahun Ini
                </button>
            </div>
            
            <div class="flex items-center gap-2">
                <input type="date" wire:model.live="startDate" class="input input-sm input-bordered rounded-lg bg-white" />
                <span class="text-stone-400 text-sm font-medium">s/d</span>
                <input type="date" wire:model.live="endDate" class="input input-sm input-bordered rounded-lg bg-white" />
            </div>

            <div wire:loading wire:target="startDate, endDate, setFilter" class="text-sm text-emerald-600 font-medium">
                <span class="loading loading-spinner loading-sm align-middle"></span> Memuat...
            </div>
        </div>
        
        <div>
            <button type="button" wire:click="exportExcel" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700 rounded-lg">
                <i class="ri-file-excel-2-line"></i> Ekspor Excel
            </button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-12">
        
        <div class="lg:col-span-4 flex flex-col gap-4">
            <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm flex-1 flex flex-col justify-center">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 bg-emerald-100 text-emerald-800 rounded-xl">
                        <i class="ri-money-dollar-circle-line text-2xl"></i>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-wider text-stone-500">Total Pendapatan</p>
                </div>
                <p class="mt-2 text-3xl xl:text-4xl font-bold text-stone-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            </article>

            <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm flex-1 flex flex-col justify-center">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 bg-emerald-100 text-emerald-800 rounded-xl">
                        <i class="ri-shopping-bag-3-line text-2xl"></i>
                    </div>
                    <p class="text-xs font-bold uppercase tracking-wider text-stone-500">Pesanan Masuk</p>
                </div>
                <p class="mt-2 text-3xl xl:text-4xl font-bold text-stone-900">{{ $totalCustomers }} <span class="text-lg font-medium text-stone-500">Transaksi</span></p>
            </article>
        </div>

        <div class="lg:col-span-8 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-stone-900">Grafik Penjualan</h3>
            </div>
            
            <div wire:ignore class="w-full flex-1">
                <div x-data="salesChartHandler(@js($chartLabels), @js($chartValues))"
                     @chart-updated.window="updateChart($event.detail.data.labels, $event.detail.data.values)"
                     x-ref="apexChart"
                     class="w-full h-full min-h-[300px]">
                </div>
            </div>
        </div>

    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-stone-900 mb-4">5 Menu Terlaris</h3>
            <div class="space-y-4">
                @forelse ($bestSellingMenus as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-stone-100 text-stone-600 font-bold">
                                {{ $loop->iteration }}
                            </div>
                            <p class="font-medium text-stone-800">{{ $item->menu_name_snapshot }}</p>
                        </div>
                        <span class="badge badge-primary badge-outline">{{ $item->total_qty }} Terjual</span>
                    </div>
                @empty
                    <p class="text-sm text-stone-500 text-center py-4">Belum ada data penjualan menu.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-stone-900 mb-4">Pendapatan Per Kasir</h3>
            <div class="space-y-4">
                @forelse ($revenuePerCashier as $cashier)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                <i class="ri-user-smile-line text-lg"></i>
                            </div>
                            <div>
                                <p class="font-medium text-stone-800">{{ $cashier->name }}</p>
                                <p class="text-xs text-stone-500">{{ $cashier->total_orders }} Transaksi</p>
                            </div>
                        </div>
                        <p class="font-semibold text-stone-900">Rp {{ number_format($cashier->total_revenue, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-stone-500 text-center py-4">Belum ada data transaksi kasir.</p>
                @endforelse
            </div>
        </article>
    </div>
</div>
