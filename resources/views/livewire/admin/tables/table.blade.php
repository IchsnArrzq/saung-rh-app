<div class="space-y-5">
    @include('admin.partials.flash')

    @error('table_status_id')
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
                        placeholder="Cari kode, nama, status, kategori, kapasitas...">
                </label>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('tables.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Meja
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kapasitas</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tables as $table)
                    <tr wire:key="table-{{ $table->id }}">
                        <td>{{ $table->code }}</td>
                        <td>{{ $table->name ?: '-' }}</td>
                        <td>{{ $table->capacity }}</td>
                        <td>{{ $table->tableCategory->name ?? '-' }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <select class="select select-bordered select-xs w-40"
                                    wire:model="statusDrafts.{{ $table->id }}">
                                    <option value="">Pilih status</option>
                                    @foreach ($statusOptions as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-sm btn-outline"
                                    wire:click="updateStatus('{{ $table->id }}')">
                                    Update
                                </button>
                            </div>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('tables.qr', $table) }}" class="btn btn-sm btn-outline">QR</a>
                                <a href="{{ route('tables.edit', $table) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    data-confirm="Hapus meja ini?"
                                    wire:click="delete('{{ $table->id }}')">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-stone-500">Belum ada data meja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $tables->links() }}</div>
</div>


