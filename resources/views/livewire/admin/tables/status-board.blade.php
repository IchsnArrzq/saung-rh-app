<div class="space-y-5" x-data="{ draggingTableId: null, fromStatusId: null, overStatusId: null }">
    @include('admin.partials.flash')

    <div wire:loading wire:target="moveTable" role="status">
        <x-alert type="info">Memindahkan meja...</x-alert>
    </div>

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" placeholder="Cari kode, nama, kategori, kapasitas..."
                    wire:model.live.debounce.300ms="search" />
            </div>

            <div class="text-xs text-secondary">
                Drag kartu meja ke kolom status tujuan.
            </div>

            @can('create', App\Models\Table::class)
                <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('tables.create')" wire:navigate>
                    Tambah meja
                </x-button>
            @endcan
        </div>
    </x-card>

    <section class="overflow-x-auto">
        <div class="flex min-w-max gap-4 pb-1">
            @forelse ($statuses as $status)
                @php($statusTables = $tablesByStatus->get($status->value, collect()))
                <article class="w-72 shrink-0 rounded-xl border border-base-300 bg-base-100 p-3">
                    <header class="mb-3 flex items-center justify-between gap-2 px-1">
                        <h3 class="text-sm font-semibold">{{ $status->label() }}</h3>
                        {{-- Jumlah meja mewarisi warna statusnya; nama token warnanya sendiri
                             bukan informasi untuk pengguna. --}}
                        <x-badge :color="$status->color()" size="sm" class="tabular-nums">{{ $statusTables->count() }}</x-badge>
                    </header>

                    <div class="min-h-72 space-y-2 rounded-xl border border-dashed border-base-300 bg-base-200 p-2 transition"
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
                            {{-- Kartu hanya bisa diseret oleh yang berhak mengubah
                                 meja. Ini lapisan kosmetik; penolakan sebenarnya
                                 ada di StatusBoard::moveTable. --}}
                            <div class="rounded-xl border border-base-300 bg-base-100 p-3 transition @can('update', $table) cursor-grab active:cursor-grabbing @endcan"
                                @can('update', $table) draggable="true" @endcan
                                wire:key="status-board-table-{{ $table->id }}"
                                x-bind:class="draggingTableId === '{{ $table->id }}' ? 'scale-[0.98] opacity-40' : ''"
                                @can('update', $table)
                                x-on:dragstart="
                                    draggingTableId = '{{ $table->id }}';
                                    fromStatusId = '{{ $status->value }}';
                                "
                                x-on:dragend="
                                    draggingTableId = null;
                                    fromStatusId = null;
                                    overStatusId = null;
                                "
                                @endcan>
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold">{{ $table->code }}</p>
                                    <x-badge color="ghost" size="sm" class="tabular-nums">Kapasitas {{ $table->capacity }}</x-badge>
                                </div>
                                <p class="mt-1 text-xs text-secondary">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                                <p class="mt-2 text-xs text-secondary">
                                    {{ $table->tableCategory?->name ? 'Kategori: '.$table->tableCategory->name : 'Tanpa kategori' }}
                                </p>
                                {{-- Urutan kolom aksi: Lihat (QR) lalu Ubah — CLAUDE.md § F. --}}
                                <div class="mt-3 flex gap-2">
                                    @can('view', $table)
                                        <x-button variant="ghost" size="sm" :href="route('tables.qr', $table)" wire:navigate>QR</x-button>
                                    @endcan
                                    @can('update', $table)
                                        <x-button variant="outline" size="sm" :href="route('tables.edit', $table)" wire:navigate>Ubah</x-button>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="flex min-h-28 items-center justify-center rounded-lg border border-dashed border-base-300 px-3 py-6 text-center text-xs text-secondary">
                                Tidak ada meja di status ini.
                            </div>
                        @endforelse
                    </div>
                </article>
            @empty
                <x-empty-state icon="ri-layout-grid-line" title="Status meja belum tersedia"
                    description="Papan ini mengikuti daftar status meja di sistem." />
            @endforelse
        </div>
    </section>
</div>
