<div class="space-y-4">
    <div class="flex items-center gap-2">
        <span class="text-sm font-semibold"><i class="ri-vip-crown-line text-secondary"></i> Pelanggan Teratas</span>
        <div class="join ml-auto">
            @foreach (['today' => 'Hari ini', 'week' => 'Minggu', 'month' => 'Bulan'] as $key => $label)
                <button wire:click="setRange('{{ $key }}')"
                    class="btn btn-xs join-item {{ $range === $key ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="card border border-base-300 bg-base-100 rounded-xl">
        <div class="card-body gap-3">
            @forelse ($customers as $i => $c)
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold w-6 text-center {{ $i === 0 ? 'text-secondary' : 'text-secondary/60' }}">
                        {{ $i === 0 ? '👑' : $i + 1 }}
                    </span>
                    <div class="grow min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold truncate">{{ $c['name'] }}</span>
                            <span class="badge badge-secondary badge-sm">Rp {{ number_format($c['total_spend'], 0, ',', '.') }}</span>
                        </div>
                        <progress class="progress progress-secondary h-1.5 mt-1" value="{{ $c['total_spend'] }}" max="{{ $maxSpend }}"></progress>
                        <div class="text-xs text-secondary mt-1">{{ $c['orders_count'] }} pesanan selesai</div>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-secondary py-8">Belum ada data pelanggan pada rentang ini.</p>
            @endforelse
        </div>
    </div>
</div>
