<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="join">
            <button wire:click="setRange('today')" class="btn btn-sm join-item {{ $range === 'today' ? 'btn-primary' : 'btn-outline' }}">Hari Ini</button>
            <button wire:click="setRange('week')" class="btn btn-sm join-item {{ $range === 'week' ? 'btn-primary' : 'btn-outline' }}">Minggu Ini</button>
            <button wire:click="setRange('month')" class="btn btn-sm join-item {{ $range === 'month' ? 'btn-primary' : 'btn-outline' }}">Bulan Ini</button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Order Selesai</p>
            <p class="mt-1 text-2xl font-bold">{{ $totalOrders }}</p>
        </div>
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Item Terjual</p>
            <p class="mt-1 text-2xl font-bold">{{ $totalItems }}</p>
        </div>
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4">
            <p class="text-xs text-secondary">Pendapatan</p>
            <p class="mt-1 text-2xl font-bold text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
        <h3 class="font-semibold text-sm mb-4">Menu & Minuman Terlaris</h3>
        <div class="space-y-3">
            @forelse ($topMenus as $i => $menu)
                <div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">
                            <span class="text-secondary mr-1">{{ $i + 1 }}.</span>{{ $menu->menu_name_snapshot }}
                        </span>
                        <span class="text-secondary">{{ (int) $menu->total_qty }} terjual · Rp {{ number_format((float) $menu->total_revenue, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-1 h-2 w-full rounded-full bg-base-200">
                        <div class="h-2 rounded-full bg-primary" style="width: {{ max(3, (int) round($menu->total_qty / $maxQty * 100)) }}%;"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-secondary">Belum ada penjualan pada rentang ini.</p>
            @endforelse
        </div>
    </div>
</div>
