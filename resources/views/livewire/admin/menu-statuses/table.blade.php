<div class="space-y-5">
    @include('admin.partials.flash')

    @error('menu_status')
        <div role="alert" class="alert alert-error">
            <span>{{ $message }}</span>
        </div>
    @enderror

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="input input-bordered flex w-full max-w-md items-center gap-2">
                    <i class="ri-search-line text-stone-400"></i>
                    <input type="text" class="grow" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, key, warna...">
                </label>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('menu-statuses.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Status
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
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
                @forelse ($menuStatuses as $menuStatus)
                    @php
                        $badgeClass = match ($menuStatus->color) {
                            'success' => 'badge-success',
                            'error' => 'badge-error',
                            'warning' => 'badge-warning',
                            'info' => 'badge-info',
                            default => 'badge-neutral',
                        };
                    @endphp
                    <tr wire:key="menu-status-{{ $menuStatus->id }}">
                        <td>{{ $menuStatus->name }}</td>
                        <td><code>{{ $menuStatus->key }}</code></td>
                        <td>
                            <span class="badge {{ $badgeClass }}">{{ $menuStatus->color ?: 'neutral' }}</span>
                        </td>
                        <td>{{ $menuStatus->sort_order }}</td>
                        <td>
                            <span class="badge {{ $menuStatus->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $menuStatus->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $menuStatus->is_default ? 'badge-primary' : 'badge-ghost' }}">
                                {{ $menuStatus->is_default ? 'Default' : '-' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('menu-statuses.edit', $menuStatus) }}" class="btn btn-xs btn-ghost">Edit</a>
                                @if (in_array($menuStatus->key, $reservedKeys, true))
                                    <button type="button" class="btn btn-xs btn-disabled">Sistem</button>
                                @else
                                    <button type="button" class="btn btn-xs btn-error text-white"
                                        onclick="if (!confirm('Hapus status menu ini?')) return false;"
                                        wire:click="delete('{{ $menuStatus->id }}')">
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-stone-500">Belum ada status menu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $menuStatuses->links() }}</div>
</div>
