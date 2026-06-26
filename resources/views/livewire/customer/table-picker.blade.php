<div>
    @if (session('warning'))
        <div class="mb-4 rounded-xl border border-warning/30 bg-warning/10 px-4 py-3 text-sm font-medium text-warning">
            {{ session('warning') }}
        </div>
    @endif

    @if ($activeTable)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-primary/30 bg-primary/10 p-4">
            <div class="text-sm">
                <p class="font-semibold"><i class="ri-restaurant-2-line text-primary"></i> Anda sedang di Meja {{ $activeTable->code }}</p>
                <p class="text-base-content/70">Lanjutkan memesan tanpa harus memilih meja lagi.</p>
            </div>
            <a href="{{ route('customer.menus.index', ['table_id' => $activeTable->id]) }}" wire:navigate class="btn btn-sm btn-primary">
                <i class="ri-bowl-line"></i> Lanjut Pesan
            </a>
        </div>
    @endif

    <section class="rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Pilih Meja</h1>
                <p class="mt-0.5 text-sm text-base-content/70">Pilih meja yang tersedia untuk mulai memesan menu.</p>
            </div>
        </div>

        <div class="relative mt-4 w-full max-w-md">
            <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></i>
            <input type="text" class="input input-bordered w-full pl-10"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari kode meja, status, atau kategori...">
        </div>
    </section>

    @php
        $badgeMap = [
            'success' => 'badge-success',
            'error' => 'badge-error',
            'warning' => 'badge-warning',
            'info' => 'badge-info',
            'secondary' => 'badge-secondary',
            'neutral' => 'badge-neutral',
        ];
    @endphp

    <section class="mt-5 grid gap-4 xl:grid-cols-4">
        @foreach ($statuses as $status)
            @php
                $badge = $badgeMap[$status->color ?: 'neutral'] ?? 'badge-neutral';
                $items = $tablesByStatus->get($status->id, collect());
            @endphp

            <article class="rounded-2xl border border-base-300 bg-base-100 p-3">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold">{{ $status->name }}</p>
                    <span class="badge {{ $badge }} badge-sm">{{ $items->count() }}</span>
                </header>

                <div class="space-y-3 rounded-xl border border-dashed border-base-300 p-2 min-h-[160px]">
                    @forelse ($items as $table)
                        @php $statusKey = $table->tableStatus?->key ?? $table->status; @endphp
                        <article class="rounded-xl border border-base-300 bg-base-100 p-3 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold">{{ $table->code }}</p>
                                    <p class="text-xs text-base-content/60">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                                </div>
                                <span class="badge badge-outline {{ $badge }} badge-sm">{{ $status->name }}</span>
                            </div>

                            <p class="mt-2 text-xs text-base-content/70">
                                Kapasitas {{ $table->capacity }} orang
                                @if ($table->tableCategory)
                                    &middot; {{ $table->tableCategory->name }}
                                @endif
                            </p>

                            <div class="mt-3">
                                @if ($statusKey === 'available')
                                    <button type="button" wire:click="selectTable('{{ $table->id }}')"
                                        class="btn btn-sm btn-primary w-full">
                                        Pilih Meja
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-disabled w-full">Tidak Tersedia</button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="rounded-lg bg-base-200/60 px-3 py-4 text-center text-xs text-base-content/50">
                            Tidak ada meja.
                        </p>
                    @endforelse
                </div>
            </article>
        @endforeach

        @if ($unassignedTables->isNotEmpty())
            <article class="rounded-2xl border border-base-300 bg-base-100 p-3">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold">Belum Punya Status</p>
                    <span class="badge badge-ghost badge-sm">{{ $unassignedTables->count() }}</span>
                </header>

                <div class="space-y-3 rounded-xl border border-dashed border-base-300 p-2">
                    @foreach ($unassignedTables as $table)
                        <article class="rounded-xl border border-base-300 bg-base-100 p-3 shadow-sm">
                            <p class="font-semibold">{{ $table->code }}</p>
                            <p class="text-xs text-base-content/60">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                        </article>
                    @endforeach
                </div>
            </article>
        @endif
    </section>
</div>
