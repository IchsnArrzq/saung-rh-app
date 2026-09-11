<div wire:poll.10s class="space-y-4">
    @can('viewAny', App\Models\TableSession::class)
        <livewire:staff.table-session-approvals wire:key="table-session-approvals" />
    @endcan

    {{-- Status summary — label & warna sudah diselesaikan di komponen. --}}
    <div class="flex flex-wrap items-center gap-2">
        @foreach ($summary as $row)
            <x-badge :color="$row['color']" size="lg" class="gap-1">
                <span class="font-bold tabular-nums">{{ $row['count'] }}</span> {{ $row['label'] }}
            </x-badge>
        @endforeach
        <span class="ml-auto inline-flex items-center gap-1 text-xs text-base-content/60">
            <i class="ri-base-station-line" aria-hidden="true"></i>
            Diperbarui otomatis tiap 10 detik
        </span>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1fr_300px]">
        {{-- Map canvas --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4 overflow-x-auto">
            <div class="relative mx-auto" style="height: {{ $rows * 120 + 8 }}px; min-width: {{ $columns * 150 }}px;">
                @foreach ($positioned as $cell)
                    @php
                        $t = $cell['model'];
                        $status = \App\Domains\Table\Enums\TableStatus::tryFrom((string) $t->status);
                        $isSelected = $selectedTableId === $t->id;
                        // Full class names per branch — Tailwind cannot scan interpolated ones.
                        // (The old code injected the token straight into `border-color`,
                        // which was never valid CSS, so the colours never showed.)
                        [$tileClass, $textClass, $dotClass] = match ($status?->color()) {
                            'success' => ['border-success bg-success/10', 'text-success', 'bg-success'],
                            'error' => ['border-error bg-error/10', 'text-error', 'bg-error'],
                            'warning' => ['border-warning bg-warning/10', 'text-warning', 'bg-warning'],
                            'info' => ['border-info bg-info/10', 'text-info', 'bg-info'],
                            'secondary' => ['border-secondary bg-secondary/10', 'text-secondary', 'bg-secondary'],
                            default => ['border-base-300 bg-base-200', 'text-base-content/60', 'bg-base-content/40'],
                        };
                    @endphp
                    <button
                        wire:click="selectTable('{{ $t->id }}')"
                        class="absolute flex flex-col items-center justify-center overflow-hidden rounded-xl border-2 px-2 text-center transition {{ $tileClass }} {{ $isSelected ? 'ring-2 ring-primary ring-offset-2' : '' }}"
                        style="left: {{ $cell['x'] * 150 }}px; top: {{ $cell['y'] * 120 }}px; width: 130px; height: 100px;">
                        <span class="w-full truncate font-bold text-sm">{{ $t->code }}</span>
                        <span class="w-full truncate text-[11px] text-secondary leading-tight">{{ $t->name }}</span>
                        <span class="mt-1 inline-flex w-full items-center justify-center gap-1 text-[10px] font-semibold uppercase tracking-wide leading-tight {{ $textClass }}">
                            <span class="h-2 w-2 shrink-0 rounded-full {{ $dotClass }}"></span>
                            <span class="truncate">{{ $status?->label() ?? '—' }}</span>
                        </span>
                        <span class="text-[10px] text-secondary mt-0.5"><i class="ri-group-line"></i> <span class="tabular-nums">{{ $t->capacity }}</span></span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Detail panel --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-5">
            @if ($selectedTable)
                <h3 class="font-bold text-lg">{{ $selectedTable['name'] }}</h3>
                <p class="text-xs text-secondary">{{ $selectedTable['code'] }}</p>

                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-secondary">Status</dt><dd class="font-semibold">{{ $selectedTable['status'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-secondary">Kapasitas</dt><dd class="tabular-nums">{{ $selectedTable['capacity'] }} kursi</dd></div>
                    <div class="flex justify-between"><dt class="text-secondary">Kategori</dt><dd>{{ $selectedTable['category'] }}</dd></div>
                </dl>

                <div class="divider my-3 text-xs">Sesi & Order</div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-secondary">Pengunjung</dt><dd class="tabular-nums">{{ $selectedTable['session_pax'] ?? '—' }} org</dd></div>
                    <div class="flex justify-between"><dt class="text-secondary">Mulai sesi</dt><dd class="tabular-nums">{{ $selectedTable['session_started'] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-secondary">Order aktif</dt>
                        <dd>
                            @if ($selectedTable['order_number'])
                                <span class="font-semibold tabular-nums">#{{ $selectedTable['order_number'] }}</span>
                                <x-status-badge :status="$selectedTable['order_status']"
                                    :enum="\App\Domains\Order\Enums\OrderStatus::class" size="xs" class="ml-1" />
                            @else — @endif
                        </dd>
                    </div>
                </dl>
            @else
                <div class="text-center text-secondary py-10">
                    <i class="ri-cursor-line text-3xl"></i>
                    <p class="mt-2 text-sm">Klik salah satu meja untuk melihat detail sesi & order.</p>
                </div>
            @endif
        </div>
    </div>
</div>
