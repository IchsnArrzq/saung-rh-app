<div class="space-y-4">
    <div class="flex items-center gap-2">
        <span class="text-sm font-semibold"><i class="ri-trophy-line text-warning"></i> KPI Pegawai Terbaik</span>
        <div class="join ml-auto">
            @foreach (['today' => 'Hari ini', 'week' => 'Minggu', 'month' => 'Bulan'] as $key => $label)
                <button wire:click="setRange('{{ $key }}')"
                    class="btn btn-xs join-item {{ $range === $key ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="card border border-base-300 bg-base-100 rounded-xl">
        <div class="card-body gap-3">
            @forelse ($staff as $i => $s)
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold w-6 text-center {{ $i === 0 ? 'text-warning' : 'text-secondary' }}">
                        {{ $i === 0 ? '🏆' : $i + 1 }}
                    </span>
                    <div class="grow min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold truncate">{{ $s['name'] }}</span>
                            <span class="badge badge-primary badge-sm">skor {{ $s['score'] }}</span>
                        </div>
                        <progress class="progress progress-primary h-1.5 mt-1" value="{{ $s['score'] }}" max="{{ $maxScore }}"></progress>
                        <div class="text-xs text-secondary mt-1 flex flex-wrap gap-x-3">
                            <span><i class="ri-hand-coin-line"></i> Rp {{ number_format($s['tips_total'], 0, ',', '.') }} ({{ $s['tips_count'] }})</span>
                            <span><i class="ri-service-line"></i> {{ $s['services_count'] }} layanan</span>
                            <span><i class="ri-customer-service-2-line"></i> {{ $s['requests_done'] }} permintaan</span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-secondary py-8">Belum ada data KPI pada rentang ini.</p>
            @endforelse
        </div>
    </div>
</div>
