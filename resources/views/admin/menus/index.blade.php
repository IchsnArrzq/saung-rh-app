<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Menu</h2>
            <a href="{{ route('menus.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Menu
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    @php
        $viewMode = $viewMode ?? 'table';
        $search = $search ?? '';
        $query = [];

        if ($search !== '') {
            $query['search'] = $search;
        }
    @endphp

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" name="search" value="{{ $search }}" placeholder="Cari nama, SKU, kategori...">
                </label>
                <button type="submit" class="btn btn-sm bg-stone-900 text-amber-50 hover:bg-stone-700">Cari</button>
                @if ($search !== '')
                    <a href="{{ route('menus.index', ['view' => $viewMode]) }}" class="btn btn-sm btn-ghost">Reset</a>
                @endif
            </form>

            <div class="join">
                <a href="{{ route('menus.index', array_merge($query, ['view' => 'table'])) }}"
                    class="join-item btn btn-sm {{ $viewMode === 'table' ? 'btn-active' : '' }}">
                    <i class="ri-table-line"></i>
                    Table View
                </a>
                <a href="{{ route('menus.index', array_merge($query, ['view' => 'card'])) }}"
                    class="join-item btn btn-sm {{ $viewMode === 'card' ? 'btn-active' : '' }}">
                    <i class="ri-layout-grid-line"></i>
                    Card View
                </a>
            </div>
        </div>
    </section>

    @if ($viewMode === 'card')
        <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($menus as $menu)
                <article class="overflow-hidden rounded-3xl border border-stone-200 bg-white">
                    <div class="aspect-[4/3] w-full bg-stone-100">
                        <img src="{{ $menu->image_url ?: 'https://picsum.photos/seed/'.urlencode((string) $menu->id).'/800/600' }}"
                            alt="{{ $menu->name }}" class="h-full w-full object-cover">
                    </div>

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">{{ $menu->category->name ?? 'Menu' }}</p>
                            <span class="badge badge-sm {{ $menu->is_available ? 'badge-success' : 'badge-error' }}">
                                {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                            </span>
                        </div>

                        <h3 class="mt-1 text-lg font-semibold text-stone-900">{{ $menu->name }}</h3>
                        <p class="mt-1 text-xs text-stone-500">SKU: {{ $menu->sku ?: '-' }}</p>
                        <p class="mt-2 text-sm text-stone-600">{{ \Illuminate\Support\Str::limit($menu->description ?: 'Menu favorit restoran.', 90) }}</p>
                        <p class="mt-3 text-lg font-bold text-emerald-800">Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-ghost">Edit</a>
                            <form action="{{ route('menus.destroy', $menu) }}" method="POST" class="ml-auto"
                                onsubmit="return confirm('Hapus menu ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-error text-white">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-center text-sm text-stone-500">
                    Belum ada data menu.
                </p>
            @endforelse
        </section>
    @else
        <div class="mt-5 overflow-x-auto rounded-2xl border border-stone-200 bg-white">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menus as $menu)
                        <tr>
                            <td>
                                <p class="font-semibold text-stone-800">{{ $menu->name }}</p>
                                <p class="text-xs text-stone-500">{{ $menu->sku ?: '-' }}</p>
                            </td>
                            <td>{{ $menu->category->name ?? '-' }}</td>
                            <td>Rp {{ number_format((float) $menu->price, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $menu->is_available ? 'badge-success' : 'badge-error' }}">
                                    {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('menus.edit', $menu) }}" class="btn btn-xs btn-ghost">Edit</a>
                                    <form action="{{ route('menus.destroy', $menu) }}" method="POST"
                                        onsubmit="return confirm('Hapus menu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-stone-500">Belum ada data menu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($menus->hasPages())
        @php
            $start = max(1, $menus->currentPage() - 2);
            $end = min($menus->lastPage(), $menus->currentPage() + 2);
        @endphp
        <nav class="mt-6 flex justify-center">
            <div class="join">
                @if ($menus->onFirstPage())
                    <button class="join-item btn btn-sm btn-disabled">«</button>
                @else
                    <a href="{{ $menus->previousPageUrl() }}" class="join-item btn btn-sm">«</a>
                @endif

                @foreach ($menus->getUrlRange($start, $end) as $page => $url)
                    <a href="{{ $url }}" class="join-item btn btn-sm {{ $page === $menus->currentPage() ? 'btn-active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if ($menus->hasMorePages())
                    <a href="{{ $menus->nextPageUrl() }}" class="join-item btn btn-sm">»</a>
                @else
                    <button class="join-item btn btn-sm btn-disabled">»</button>
                @endif
            </div>
        </nav>
    @endif
</x-app-layout>
