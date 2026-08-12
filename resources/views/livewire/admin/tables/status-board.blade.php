<div class="space-y-5" x-data="{ draggingTableId: null, fromStatusId: null, overStatusId: null }">
    @include('admin.partials.flash')

    <div wire:loading wire:target="moveTable" role="status" class="alert alert-info">
        <span>Memindahkan meja...</span>
    </div>

    <section class="rounded-2xl border border-base-300 bg-base-100 p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-md">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari kode, nama, kategori, kapasitas...">
                </div>

            </div>

            <div class="text-xs text-secondary">
                Drag kartu meja ke kolom status tujuan.
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('tables.create')">
                Tambah Meja
            </x-button>
        </div>
    </section>

    <section class="overflow-x-auto">
        <div class="flex min-w-max gap-4 pb-1">
            @forelse ($statuses as $status)
                @php
                    // Full class names per branch — Tailwind cannot scan interpolated ones.
                    $headerBadgeClass = match ($status->color()) {
                        'success' => 'badge-success',
                        'error' => 'badge-error',
                        'warning' => 'badge-warning',
                        'info' => 'badge-info',
                        'secondary' => 'badge-secondary',
                        default => 'badge-neutral',
                    };
                    $statusTables = $tablesByStatus->get($status->value, collect());
                @endphp
                <article class="w-[300px] shrink-0 rounded-2xl border border-base-300 bg-base-100 p-3 shadow-sm">
                    <header class="mb-3 flex items-center justify-between gap-2 px-1">
                        <div>
                            <h3 class="text-sm font-semibold text-base-content">{{ $status->label() }}</h3>
                            <p class="text-xs text-secondary">Key: {{ $status->value }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge {{ $headerBadgeClass }}">{{ $status->color() }}</span>
                            <span class="badge badge-outline">{{ $statusTables->count() }}</span>
                        </div>
                    </header>

                    <div class="min-h-[280px] space-y-2 rounded-xl border border-dashed border-base-300 bg-base-200 p-2 transition"
                        x-bind:class="overStatusId === '{{ $status->value }}' ? 'ring-2 ring-primary/40 ring-offset-2 ring-offset-base-100 border-primary/50' : ''"
                        x-on:dragenter.prevent="overStatusId = '{{ $status->value }}'"
                        x-on:dragover.prevent
                        x-on:dragleave.prevent="if (overStatusId === '{{ $status->value }}') overStatusId = null"
                        x-on:drop.prevent="
                            if (!draggingTableId) return;
                            $wire.moveTable(draggingTableId, '{{ $status->value }}');
                            draggingTableId = null;
                            fromStatusId = null;
                            overStatusId = null;
                        ">
                        @forelse ($statusTables as $table)
                            <div class="cursor-grab rounded-xl border border-base-300 bg-base-100 p-3 shadow-sm transition active:cursor-grabbing"
                                draggable="true" wire:key="status-board-table-{{ $table->id }}"
                                x-bind:class="draggingTableId === '{{ $table->id }}' ? 'scale-[0.98] opacity-40' : ''"
                                x-on:dragstart="
                                    draggingTableId = '{{ $table->id }}';
                                    fromStatusId = '{{ $status->value }}';
                                "
                                x-on:dragend="
                                    draggingTableId = null;
                                    fromStatusId = null;
                                    overStatusId = null;
                                ">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-base-content">{{ $table->code }}</p>
                                    <span class="badge badge-ghost">Kapasitas {{ $table->capacity }}</span>
                                </div>
                                <p class="mt-1 text-xs text-secondary">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                                <p class="mt-2 text-xs text-secondary">
                                    {{ $table->tableCategory?->name ? 'Kategori: '.$table->tableCategory->name : 'Tanpa kategori' }}
                                </p>
                                <div class="mt-3 flex gap-2">
                                    <x-button variant="warning" size="sm" :href="route('tables.edit', $table)">Edit</x-button>
                                    <x-button variant="outline" size="sm" :href="route('tables.qr', $table)">QR</x-button>
                                </div>
                            </div>
                        @empty
                            <div class="flex min-h-[120px] items-center justify-center rounded-lg border border-dashed border-base-300 px-3 py-6 text-center text-xs text-secondary">
                                Tidak ada meja di status ini.
                            </div>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-base-300 bg-base-100 p-5 text-center text-sm text-secondary">
                    Status meja belum tersedia.
                </div>
            @endforelse
        </div>
    </section>
</div>
