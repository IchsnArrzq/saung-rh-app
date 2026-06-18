<x-customer-layout>
    <section class="rounded-2xl border border-stone-200 bg-white p-5 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">Pilih Meja</h1>
                <p class="mt-1 text-sm text-stone-600">Pilih meja tersedia untuk mulai pesan menu.</p>
            </div>
        </div>

        <form method="GET" class="mt-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-md">
                <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                <input type="text" class="input input-bordered w-full pl-10" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari kode meja, status, kategori...">
            </div>
            <button type="submit" class="btn btn-sm bg-stone-900 text-amber-50 hover:bg-stone-700">Cari</button>
            @if (($search ?? '') !== '')
                <a href="{{ route('customer.menus.tables') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </section>

    @php
        $colorMap = [
            'success' => [
                'column' => 'border-emerald-200 bg-emerald-50/40',
                'dot' => 'bg-emerald-500',
                'badge' => 'badge-success',
            ],
            'error' => [
                'column' => 'border-rose-200 bg-rose-50/40',
                'dot' => 'bg-rose-500',
                'badge' => 'badge-error',
            ],
            'warning' => [
                'column' => 'border-amber-200 bg-amber-50/40',
                'dot' => 'bg-amber-500',
                'badge' => 'badge-warning',
            ],
            'info' => [
                'column' => 'border-sky-200 bg-sky-50/40',
                'dot' => 'bg-sky-500',
                'badge' => 'badge-info',
            ],
            'neutral' => [
                'column' => 'border-stone-200 bg-stone-50/70',
                'dot' => 'bg-stone-500',
                'badge' => 'badge-neutral',
            ],
        ];
    @endphp

    <section class="mt-5 grid gap-4 xl:grid-cols-4">
        @foreach ($statuses as $status)
            @php
                $meta = $colorMap[$status->color ?: 'neutral'] ?? $colorMap['neutral'];
                $items = $tablesByStatus->get($status->id, collect());
            @endphp

            <article class="rounded-2xl border p-3 {{ $meta['column'] }}">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full {{ $meta['dot'] }}"></span>
                        <p class="text-sm font-semibold text-stone-800">{{ $status->name }}</p>
                    </div>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-stone-600">
                        {{ $items->count() }}
                    </span>
                </header>

                <div class="space-y-3 rounded-xl border border-dashed border-stone-300/70 p-2 min-h-[160px]">
                    @forelse ($items as $table)
                        @php
                            $statusKey = $table->tableStatus?->key ?? $table->status;
                        @endphp
                        <article class="rounded-xl border border-stone-200 bg-white p-3 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-stone-900">{{ $table->code }}</p>
                                    <p class="text-xs text-stone-500">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                                </div>
                                <span class="badge badge-outline {{ $meta['badge'] }}">{{ $status->name }}</span>
                            </div>

                            <p class="mt-2 text-xs text-stone-600">
                                Kapasitas {{ $table->capacity }} orang
                                @if ($table->tableCategory)
                                    • {{ $table->tableCategory->name }}
                                @endif
                            </p>

                            <div class="mt-3">
                                @if ($statusKey === 'available')
                                    <a href="{{ route('customer.menus.index', ['table_id' => $table->id]) }}"
                                        class="btn btn-sm w-full bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                                        Pilih Meja
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-disabled w-full">Tidak Tersedia</button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="rounded-lg bg-white/80 px-3 py-4 text-center text-xs text-stone-500">
                            Tidak ada meja.
                        </p>
                    @endforelse
                </div>
            </article>
        @endforeach

        @if ($unassignedTables->isNotEmpty())
            <article class="rounded-2xl border border-orange-200 bg-orange-50/50 p-3">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                        <p class="text-sm font-semibold text-stone-800">Belum Punya Status</p>
                    </div>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-stone-600">
                        {{ $unassignedTables->count() }}
                    </span>
                </header>

                <div class="space-y-3 rounded-xl border border-dashed border-orange-300/70 p-2">
                    @foreach ($unassignedTables as $table)
                        <article class="rounded-xl border border-stone-200 bg-white p-3 shadow-sm">
                            <p class="font-semibold text-stone-900">{{ $table->code }}</p>
                            <p class="text-xs text-stone-500">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                        </article>
                    @endforeach
                </div>
            </article>
        @endif
    </section>
</x-customer-layout>
