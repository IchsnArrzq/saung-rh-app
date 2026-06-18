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

                <label class="label cursor-pointer justify-start gap-2 px-0">
                    <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                        wire:model.live="showInactiveStatuses">
                    <span class="label-text text-sm">Tampilkan status nonaktif</span>
                </label>
            </div>

            <div class="text-xs text-secondary">
                Drag kartu meja ke kolom status tujuan.
            </div>

            <a href="{{ route('tables.create') }}" class="btn btn-sm btn-primary">
                <i class="ri-add-line"></i>
                Tambah Meja
            </a>
        </div>
    </section>

    <section class="overflow-x-auto">
        <div class="flex min-w-max gap-4 pb-1">
            @forelse ($statuses as $status)
                @php
                    $headerBadgeClass = match ($status->color) {
                        'success' => 'badge-success',
                        'error' => 'badge-error',
                        'warning' => 'badge-warning',
                        'info' => 'badge-info',
                        default => 'badge-neutral',
                    };
                @endphp
                <article class="w-[300px] shrink-0 rounded-2xl border border-base-300 bg-base-100 p-3 shadow-sm">
                    <header class="mb-3 flex items-center justify-between gap-2 px-1">
                        <div>
                            <h3 class="text-sm font-semibold text-base-content">{{ $status->name }}</h3>
                            <p class="text-xs text-secondary">Key: {{ $status->key }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge {{ $headerBadgeClass }}">{{ $status->color ?: 'neutral' }}</span>
                            <span class="badge badge-outline">{{ $status->tables->count() }}</span>
                        </div>
                    </header>

                    <div class="min-h-[280px] space-y-2 rounded-xl border border-dashed border-base-300 bg-base-200 p-2 transition"
                        x-bind:class="overStatusId === '{{ $status->id }}' ? 'ring-2 ring-primary/40 ring-offset-2 ring-offset-base-100 border-primary/50' : ''"
                        x-on:dragenter.prevent="overStatusId = '{{ $status->id }}'"
                        x-on:dragover.prevent
                        x-on:dragleave.prevent="if (overStatusId === '{{ $status->id }}') overStatusId = null"
                        x-on:drop.prevent="
                            if (!draggingTableId) return;
                            $wire.moveTable(draggingTableId, '{{ $status->id }}');
                            draggingTableId = null;
                            fromStatusId = null;
                            overStatusId = null;
                        ">
                        @forelse ($status->tables as $table)
                            <div class="cursor-grab rounded-xl border border-base-300 bg-base-100 p-3 shadow-sm transition active:cursor-grabbing"
                                draggable="true" wire:key="status-board-table-{{ $table->id }}"
                                x-bind:class="draggingTableId === '{{ $table->id }}' ? 'scale-[0.98] opacity-40' : ''"
                                x-on:dragstart="
                                    draggingTableId = '{{ $table->id }}';
                                    fromStatusId = '{{ $status->id }}';
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
                                    <a href="{{ route('tables.edit', $table) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="{{ route('tables.qr', $table) }}" class="btn btn-sm btn-outline">QR</a>
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
