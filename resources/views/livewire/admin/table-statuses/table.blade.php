<div class="space-y-5">
    @include('admin.partials.flash')

    @error('table_status')
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

            <a href="{{ route('table-statuses.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
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
                    <tr wire:key="table-status-{{ $tableStatus->id }}">
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
                                <a href="{{ route('table-statuses.edit', $tableStatus) }}" class="btn btn-sm btn-warning">Edit</a>
                                @if (in_array($tableStatus->key, $reservedKeys, true))
                                    <button type="button" class="btn btn-sm btn-disabled">Sistem</button>
                                @else
                                    <button type="button" class="btn btn-sm btn-error text-white"
                                        onclick="if (!confirm('Hapus status meja ini?')) return false;"
                                        wire:click="delete('{{ $tableStatus->id }}')">
                                        Hapus
                                    </button>
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

    <div>{{ $tableStatuses->links() }}</div>
</div>

