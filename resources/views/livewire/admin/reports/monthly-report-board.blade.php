<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-4 bg-white p-4 rounded-3xl border border-stone-200 shadow-sm">
        <div class="form-control w-full max-w-xs">
            <label class="label"><span class="label-text">Pilih Bulan</span></label>
            <select wire:model.live="month" class="select select-bordered">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                @endfor
            </select>
        </div>
        <div class="form-control w-full max-w-xs">
            <label class="label"><span class="label-text">Pilih Tahun</span></label>
            <select wire:model.live="year" class="select select-bordered">
                @for($y = now()->year; $y >= 2020; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div wire:loading wire:target="month, year" class="mt-8 text-sm text-stone-500">
            Memuat data...
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-2">
        <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-3 bg-emerald-100 text-emerald-700 rounded-xl">
                    <span class="text-xl font-bold">Rp</span>
                </div>
                <p class="text-xs font-bold uppercase tracking-wider text-stone-500">Pendapatan Bulanan</p>
            </div>
            <p class="mt-2 text-4xl font-bold text-stone-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
        </article>

        <article class="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-3 bg-amber-100 text-amber-700 rounded-xl">
                    <span class="text-xl font-bold">#</span>
                </div>
                <p class="text-xs font-bold uppercase tracking-wider text-stone-500">Pesanan Masuk</p>
            </div>
            <p class="mt-2 text-4xl font-bold text-stone-900">{{ $totalCustomers }} <span class="text-lg font-medium text-stone-500">Pesanan</span></p>
        </article>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-stone-900 mb-4 px-2">Top 5 Menu Bulanan</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="bg-stone-50 rounded-l-xl text-stone-500">Menu</th>
                            <th class="bg-stone-50 rounded-r-xl text-right text-stone-500">Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bestSellingMenus as $menu)
                            <tr class="border-b-stone-100 last:border-none">
                                <td class="font-semibold text-stone-800">{{ $menu->menu_name_snapshot }}</td>
                                <td class="text-right">
                                    <span class="badge bg-emerald-800 text-white border-none badge-sm p-3">{{ $menu->total_qty }} porsi</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-stone-500 py-6">Belum ada data penjualan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-stone-900 mb-4 px-2">Kinerja Kasir</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="bg-stone-50 rounded-l-xl text-stone-500">Nama Kasir</th>
                            <th class="bg-stone-50 text-center text-stone-500">Order</th>
                            <th class="bg-stone-50 rounded-r-xl text-right text-stone-500">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($revenuePerCashier as $cashier)
                            <tr class="border-b-stone-100 last:border-none">
                                <td class="font-semibold text-stone-800">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                          <div class="bg-stone-200 text-stone-700 rounded-full w-8 h-8 flex items-center justify-center">
                                            <span class="text-xs">{{ substr($cashier->name, 0, 1) }}</span>
                                          </div>
                                        </div>
                                        {{ $cashier->name }}
                                    </div>
                                </td>
                                <td class="text-center text-stone-600">{{ $cashier->total_orders }}</td>
                                <td class="text-right font-bold text-emerald-800">Rp {{ number_format($cashier->total_revenue, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-stone-500 py-6">Belum ada transaksi tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</div>
