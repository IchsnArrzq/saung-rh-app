<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Status Meja</h2>
            <a href="{{ route('table-statuses.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Status
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                <i class="ri-search-line text-stone-400"></i>
                <input type="text" class="grow" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari nama, key, warna...">
            </label>
            <button type="submit" class="btn btn-sm bg-stone-900 text-amber-50 hover:bg-stone-700">Cari</button>
            @if (($search ?? '') !== '')
                <a href="{{ route('table-statuses.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </section>

    <div class="mt-5 overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Key</th>
                    <th>Warna</th>
                    <th>Urutan</th>
                    <th>Aktif</th>
                    <th>Default</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tableStatuses as $tableStatus)
                    @php
                        $badgeClass = match ($tableStatus->color) {
                            'success' => 'badge-success',
                            'error' => 'badge-error',
                            'warning' => 'badge-warning',
                            'info' => 'badge-info',
                            default => 'badge-neutral',
                        };
                    @endphp
                    <tr>
                        <td>{{ $tableStatus->name }}</td>
                        <td><code>{{ $tableStatus->key }}</code></td>
                        <td>
                            <span class="badge {{ $badgeClass }}">{{ $tableStatus->color ?: 'neutral' }}</span>
                        </td>
                        <td>{{ $tableStatus->sort_order }}</td>
                        <td>
                            <span class="badge {{ $tableStatus->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $tableStatus->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $tableStatus->is_default ? 'badge-primary' : 'badge-ghost' }}">
                                {{ $tableStatus->is_default ? 'Default' : '-' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('table-statuses.edit', $tableStatus) }}" class="btn btn-xs btn-ghost">Edit</a>
                                @if (in_array($tableStatus->key, ['available', 'occupied', 'order_in', 'cleaning'], true))
                                    <button type="button" class="btn btn-xs btn-disabled">Sistem</button>
                                @else
                                    <form action="{{ route('table-statuses.destroy', $tableStatus) }}" method="POST"
                                        onsubmit="return confirm('Hapus status meja ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-stone-500">Belum ada status meja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($tableStatuses->hasPages())
        @php
            $start = max(1, $tableStatuses->currentPage() - 2);
            $end = min($tableStatuses->lastPage(), $tableStatuses->currentPage() + 2);
        @endphp
        <nav class="mt-6 flex justify-center">
            <div class="join">
                @if ($tableStatuses->onFirstPage())
                    <button class="join-item btn btn-sm btn-disabled">«</button>
                @else
                    <a href="{{ $tableStatuses->previousPageUrl() }}" class="join-item btn btn-sm">«</a>
                @endif

                @foreach ($tableStatuses->getUrlRange($start, $end) as $page => $url)
                    <a href="{{ $url }}" class="join-item btn btn-sm {{ $page === $tableStatuses->currentPage() ? 'btn-active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($tableStatuses->hasMorePages())
                    <a href="{{ $tableStatuses->nextPageUrl() }}" class="join-item btn btn-sm">»</a>
                @else
                    <button class="join-item btn btn-sm btn-disabled">»</button>
                @endif
            </div>
        </nav>
    @endif
</x-app-layout>
