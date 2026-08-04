<div wire:poll.10s class="space-y-4">
    {{-- Status summary --}}
    <div class="flex flex-wrap gap-2">
        @foreach ($summary as $key => $count)
            <span class="badge badge-lg badge-ghost gap-1">
                <span class="font-bold">{{ $count }}</span> {{ ucfirst(str_replace('_', ' ', $key)) }}
            </span>
        @endforeach
        <span class="badge badge-lg badge-outline ml-auto gap-1">
            <i class="ri-base-station-line text-success"></i> Live · auto-refresh 10s
        </span>
    </div>

    <div class="grid gap-4 lg:grid-cols-[1fr_300px]">
        {{-- Map canvas --}}
        <div class="card border border-base-300 bg-base-100 rounded-xl p-4 overflow-x-auto">
            <div class="relative mx-auto" style="height: {{ $rows * 120 + 8 }}px; min-width: {{ 5 * 150 }}px;">
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
                        class="absolute flex flex-col items-center justify-center rounded-xl border-2 text-center transition hover:scale-105 {{ $tileClass }} {{ $isSelected ? 'ring-2 ring-primary ring-offset-2' : '' }}"
                        style="left: {{ $cell['x'] * 150 }}px; top: {{ $cell['y'] * 120 }}px; width: 130px; height: 100px;">
                        <span class="font-bold text-sm">{{ $t->code }}</span>
                        <span class="text-[11px] text-secondary leading-tight">{{ $t->name }}</span>
                        <span class="mt-1 inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide {{ $textClass }}">
                            <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>
                            {{ $status?->label() ?? '—' }}
                        </span>
                        <span class="text-[10px] text-secondary mt-0.5"><i class="ri-group-line"></i> {{ $t->capacity }}</span>
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
                    <div class="flex justify-between"><dt class="text-secondary">Kapasitas</dt><dd>{{ $selectedTable['capacity'] }} kursi</dd></div>
                    <div class="flex justify-between"><dt class="text-secondary">Kategori</dt><dd>{{ $selectedTable['category'] }}</dd></div>
                </dl>

                <div class="divider my-3 text-xs">Sesi & Order</div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-secondary">Pengunjung</dt><dd>{{ $selectedTable['session_pax'] ?? '—' }} org</dd></div>
                    <div class="flex justify-between"><dt class="text-secondary">Mulai sesi</dt><dd>{{ $selectedTable['session_started'] ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-secondary">Order aktif</dt>
                        <dd>
                            @if ($selectedTable['order_number'])
                                <span class="font-semibold">#{{ $selectedTable['order_number'] }}</span>
                                <span class="badge badge-xs badge-info ml-1">{{ $selectedTable['order_status'] }}</span>
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
