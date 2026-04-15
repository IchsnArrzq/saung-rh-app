<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Kategori Meja</h2>
            <a href="{{ route('table-categories.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Kategori
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                <i class="ri-search-line text-stone-400"></i>
                <input type="text" class="grow" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari nama, slug, deskripsi...">
            </label>
            <button type="submit" class="btn btn-sm bg-stone-900 text-amber-50 hover:bg-stone-700">Cari</button>
            @if (($search ?? '') !== '')
                <a href="{{ route('table-categories.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </section>

    <div class="mt-5 overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tableCategories as $tableCategory)
                    <tr>
                        <td>{{ $tableCategory->name }}</td>
                        <td>{{ $tableCategory->slug }}</td>
                        <td>{{ $tableCategory->sort_order }}</td>
                        <td>
                            <span class="badge {{ $tableCategory->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $tableCategory->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('table-categories.edit', $tableCategory) }}" class="btn btn-xs btn-ghost">Edit</a>
                                <form action="{{ route('table-categories.destroy', $tableCategory) }}" method="POST"
                                    onsubmit="return confirm('Hapus kategori meja ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-stone-500">Belum ada kategori meja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($tableCategories->hasPages())
        @php
            $start = max(1, $tableCategories->currentPage() - 2);
            $end = min($tableCategories->lastPage(), $tableCategories->currentPage() + 2);
        @endphp
        <nav class="mt-6 flex justify-center">
            <div class="join">
                @if ($tableCategories->onFirstPage())
                    <button class="join-item btn btn-sm btn-disabled">«</button>
                @else
                    <a href="{{ $tableCategories->previousPageUrl() }}" class="join-item btn btn-sm">«</a>
                @endif

                @foreach ($tableCategories->getUrlRange($start, $end) as $page => $url)
                    <a href="{{ $url }}" class="join-item btn btn-sm {{ $page === $tableCategories->currentPage() ? 'btn-active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($tableCategories->hasMorePages())
                    <a href="{{ $tableCategories->nextPageUrl() }}" class="join-item btn btn-sm">»</a>
                @else
                    <button class="join-item btn btn-sm btn-disabled">»</button>
                @endif
            </div>
        </nav>
    @endif
</x-app-layout>
