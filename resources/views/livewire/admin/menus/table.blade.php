<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, SKU, kategori...">
                </label>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <div class="join">
                    <button type="button" class="join-item btn btn-sm {{ $viewMode === 'table' ? 'btn-active' : '' }}"
                        wire:click="$set('viewMode', 'table')">
                        <i class="ri-table-line"></i>
                        Table View
                    </button>
                    <button type="button" class="join-item btn btn-sm {{ $viewMode === 'card' ? 'btn-active' : '' }}"
                        wire:click="$set('viewMode', 'card')">
                        <i class="ri-layout-grid-line"></i>
                        Card View
                    </button>
                </div>

                <a href="{{ route('menus.create') }}"
                    class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                    <i class="ri-add-line"></i>
                    Tambah Menu
                </a>
            </div>
        </div>
    </section>

    @if ($viewMode === 'card')
        <section class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse ($menus as $menu)
                <article class="overflow-hidden rounded-3xl border border-stone-200 bg-white"
                    wire:key="menu-card-{{ $menu->id }}">
                    <div class="aspect-[4/3] w-full bg-stone-100">
                        <img src="{{ $menu->image_url ?: 'https://picsum.photos/seed/' . urlencode((string) $menu->id) . '/800/600' }}"
                            alt="{{ $menu->name }}" class="h-full w-full object-cover">
                    </div>

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">
                                {{ $menu->category->name ?? 'Menu' }}</p>
                            <span class="badge badge-sm {{ $menu->is_available ? 'badge-success' : 'badge-error' }}">
                                {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                            </span>
                        </div>

                        <h3 class="mt-1 text-lg font-semibold text-stone-900">{{ $menu->name }}</h3>
                        <p class="mt-1 text-xs text-stone-500">SKU: {{ $menu->sku ?: '-' }}</p>
                        <p class="mt-2 text-sm text-stone-600">
                            {{ \Illuminate\Support\Str::limit($menu->description ?: 'Menu favorit restoran.', 90) }}
                        </p>
                        <p class="mt-3 text-lg font-bold text-emerald-800">Rp
                            {{ number_format((float) $menu->price, 0, ',', '.') }}</p>

                        <div class="mt-4 gap-2">
                            <a href="{{ route('menus.edit', $menu) }}" class="btn btn-sm btn-warning">Edit</a>
                            <button type="button" class="btn btn-sm btn-error ml-auto text-white"
                                onclick="if (!confirm('Hapus menu ini?')) return false;"
                                wire:click="delete('{{ $menu->id }}')">
                                Hapus
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <p
                    class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-5 text-center text-sm text-stone-500">
                    Belum ada data menu.
                </p>
            @endforelse
        </section>
    @else
        <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
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
                        <tr wire:key="menu-row-{{ $menu->id }}">
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
                                    <button type="button" class="btn btn-xs btn-error text-white"
                                        onclick="if (!confirm('Hapus menu ini?')) return false;"
                                        wire:click="delete('{{ $menu->id }}')">
                                        Hapus
                                    </button>
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

    <div>{{ $menus->links() }}</div>
</div>
