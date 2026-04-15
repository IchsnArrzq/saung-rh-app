<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold">Visual Denah Meja</h2>
                <p class="mt-1 text-sm text-stone-600">Drag card meja ke kolom status untuk ubah status secara cepat.</p>
            </div>
            <a href="{{ route('tables.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Meja
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

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

        $statusIdByKey = $statusOptions->pluck('id', 'key');

        $tablesByStatus = $tables->getCollection()->groupBy(function ($table) use ($statusIdByKey) {
            if ($table->table_status_id) {
                return $table->table_status_id;
            }

            $legacyStatusId = $statusIdByKey->get($table->status);

            return $legacyStatusId ?: '__unassigned__';
        });

        $unassignedTables = $tablesByStatus->get('__unassigned__', collect());
    @endphp

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                <i class="ri-search-line text-stone-400"></i>
                <input type="text" class="grow" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari kode, nama, status, kategori, kapasitas...">
            </label>
            <button type="submit" class="btn btn-sm bg-stone-900 text-amber-50 hover:bg-stone-700">Cari</button>
            @if (($search ?? '') !== '')
                <a href="{{ route('tables.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </section>

    <section class="mt-5 grid gap-4 xl:grid-cols-4">
        @if ($statusOptions->isEmpty())
            <article class="rounded-2xl border border-stone-200 bg-white p-5">
                <p class="text-sm text-stone-600">Belum ada status meja. Tambahkan status dulu sebelum mengelola denah meja.</p>
                <a href="{{ route('table-statuses.create') }}" class="btn btn-sm mt-3 bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                    Tambah Status Meja
                </a>
            </article>
        @endif

        @foreach ($statusOptions as $status)
            @php
                $meta = $colorMap[$status->color ?: 'neutral'] ?? $colorMap['neutral'];
                $items = $tablesByStatus->get($status->id, collect());
            @endphp

            <article class="status-column rounded-2xl border p-3 {{ $meta['column'] }}" data-status-id="{{ $status->id }}">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full {{ $meta['dot'] }}"></span>
                        <p class="text-sm font-semibold text-stone-800">{{ $status->name }}</p>
                    </div>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-stone-600 column-count">
                        {{ $items->count() }}
                    </span>
                </header>

                <div class="status-dropzone min-h-[160px] space-y-3 rounded-xl border border-dashed border-stone-300/70 p-2"
                    data-status-id="{{ $status->id }}" data-label="{{ $status->name }}" data-badge-class="{{ $meta['badge'] }}">
                    @forelse ($items as $table)
                        <article class="table-card rounded-xl border border-stone-200 bg-white p-3 shadow-sm"
                            draggable="true" data-table-id="{{ $table->id }}" data-status-id="{{ $status->id }}"
                            data-status-url="{{ route('tables.status', $table) }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-stone-900">{{ $table->code }}</p>
                                    <p class="text-xs text-stone-500">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                                </div>
                                <span class="table-status-badge badge badge-outline {{ $meta['badge'] }}">{{ $status->name }}</span>
                            </div>

                            <p class="mt-2 text-xs text-stone-600">
                                Kapasitas {{ $table->capacity }} orang
                                @if ($table->tableCategory)
                                    • {{ $table->tableCategory->name }}
                                @endif
                            </p>

                            <div class="mt-3 flex items-center gap-1.5">
                                <a href="{{ route('tables.qr', $table) }}" class="btn btn-xs btn-outline">QR</a>
                                <a href="{{ route('tables.edit', $table) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <form action="{{ route('tables.destroy', $table) }}" method="POST" class="ml-auto"
                                    onsubmit="return confirm('Hapus meja ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p class="empty-state rounded-lg bg-white/80 px-3 py-4 text-center text-xs text-stone-500">
                            Belum ada meja.
                        </p>
                    @endforelse
                </div>
            </article>
        @endforeach

        @if ($unassignedTables->isNotEmpty())
            <article class="status-column rounded-2xl border border-orange-200 bg-orange-50/50 p-3">
                <header class="mb-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                        <p class="text-sm font-semibold text-stone-800">Belum Punya Status</p>
                    </div>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-stone-600">
                        {{ $unassignedTables->count() }}
                    </span>
                </header>

                <div class="min-h-[160px] space-y-3 rounded-xl border border-dashed border-orange-300/70 p-2">
                    @foreach ($unassignedTables as $table)
                        <article class="rounded-xl border border-stone-200 bg-white p-3 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-stone-900">{{ $table->code }}</p>
                                    <p class="text-xs text-stone-500">{{ $table->name ?: 'Tanpa nama meja' }}</p>
                                </div>
                                <span class="badge badge-outline badge-warning">Belum punya status</span>
                            </div>
                            <p class="mt-2 text-xs text-stone-600">Edit meja untuk memilih status.</p>
                        </article>
                    @endforeach
                </div>
            </article>
        @endif
    </section>

    @if ($tables->hasPages())
        @php
            $start = max(1, $tables->currentPage() - 2);
            $end = min($tables->lastPage(), $tables->currentPage() + 2);
        @endphp
        <nav class="mt-6 flex justify-center">
            <div class="join">
                @if ($tables->onFirstPage())
                    <button class="join-item btn btn-sm btn-disabled">«</button>
                @else
                    <a href="{{ $tables->previousPageUrl() }}" class="join-item btn btn-sm">«</a>
                @endif

                @foreach ($tables->getUrlRange($start, $end) as $page => $url)
                    <a href="{{ $url }}" class="join-item btn btn-sm {{ $page === $tables->currentPage() ? 'btn-active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($tables->hasMorePages())
                    <a href="{{ $tables->nextPageUrl() }}" class="join-item btn btn-sm">»</a>
                @else
                    <button class="join-item btn btn-sm btn-disabled">»</button>
                @endif
            </div>
        </nav>
    @endif

    <script>
        (() => {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const dropzones = Array.from(document.querySelectorAll('.status-dropzone'));

            if (!token || dropzones.length === 0) {
                return;
            }

            const updateColumnMeta = () => {
                dropzones.forEach((zone) => {
                    const column = zone.closest('.status-column');
                    if (!column) return;

                    const count = zone.querySelectorAll('.table-card').length;
                    const countEl = column.querySelector('.column-count');
                    if (countEl) {
                        countEl.textContent = String(count);
                    }

                    const emptyState = zone.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.classList.toggle('hidden', count > 0);
                    }
                });
            };

            const attachDraggable = (card) => {
                card.addEventListener('dragstart', (event) => {
                    card.classList.add('opacity-60');
                    event.dataTransfer?.setData('text/table-id', card.dataset.tableId ?? '');
                    event.dataTransfer?.setData('text/current-status-id', card.dataset.statusId ?? '');
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('opacity-60');
                });
            };

            document.querySelectorAll('.table-card').forEach((card) => {
                attachDraggable(card);
            });

            dropzones.forEach((zone) => {
                zone.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    zone.classList.add('ring', 'ring-emerald-300');
                });

                zone.addEventListener('dragleave', () => {
                    zone.classList.remove('ring', 'ring-emerald-300');
                });

                zone.addEventListener('drop', async (event) => {
                    event.preventDefault();
                    zone.classList.remove('ring', 'ring-emerald-300');

                    const tableId = event.dataTransfer?.getData('text/table-id');
                    const currentStatusId = event.dataTransfer?.getData('text/current-status-id');
                    const nextStatusId = zone.dataset.statusId;

                    if (!tableId || !nextStatusId || nextStatusId === currentStatusId) {
                        return;
                    }

                    const card = document.querySelector(`.table-card[data-table-id="${tableId}"]`);
                    if (!(card instanceof HTMLElement)) {
                        return;
                    }

                    const endpoint = card.dataset.statusUrl;
                    if (!endpoint) {
                        return;
                    }

                    try {
                        const response = await fetch(endpoint, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                            },
                            body: JSON.stringify({
                                table_status_id: nextStatusId,
                            }),
                        });

                        if (!response.ok) {
                            throw new Error('Gagal update status.');
                        }

                        zone.appendChild(card);
                        card.dataset.statusId = nextStatusId;

                        const badge = card.querySelector('.table-status-badge');
                        if (badge) {
                            const text = zone.dataset.label ?? '';
                            badge.textContent = text;
                            badge.className = `table-status-badge badge badge-outline ${zone.dataset.badgeClass ?? ''}`.trim();
                        }

                        updateColumnMeta();
                    } catch (error) {
                        alert('Status meja gagal diperbarui. Coba lagi.');
                    }
                });
            });

            updateColumnMeta();
        })();
    </script>
</x-app-layout>
