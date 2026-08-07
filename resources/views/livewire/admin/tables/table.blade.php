<div class="space-y-5">
    @include('admin.partials.flash')

    @error('status')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-search-input class="max-w-md" wire:model.live.debounce.300ms="search"
                    placeholder="Cari kode, nama, status, kategori, kapasitas..." label="Cari meja" />

                @if ($search !== '')
                    <x-button variant="ghost" size="sm" wire:click="$set('search', '')">Reset</x-button>
                @endif
            </div>

            <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('tables.create')">
                Tambah Meja
            </x-button>
        </div>
    </x-card>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Kapasitas</th>
                <th>Kategori</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse ($tables as $table)
            <tr wire:key="table-{{ $table->id }}">
                <td>{{ $table->code }}</td>
                <td>{{ $table->name ?: '-' }}</td>
                <td>{{ $table->capacity }}</td>
                <td>{{ $table->tableCategory->name ?? '-' }}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <x-select :bare="true" size="xs" class="w-40" placeholder="Pilih status"
                            label="Status meja {{ $table->code }}"
                            wire:model="statusDrafts.{{ $table->id }}">
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </x-select>

                        <x-button variant="outline" size="sm" wire:click="updateStatus('{{ $table->id }}')"
                            loading="updateStatus('{{ $table->id }}')">
                            Update
                        </x-button>
                    </div>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="outline" size="sm" :href="route('tables.qr', $table)">QR</x-button>
                        <x-button variant="warning" size="sm" :href="route('tables.edit', $table)">Edit</x-button>
                        <x-button variant="error" size="sm" class="text-white"
                            data-confirm="Hapus meja ini?"
                            wire:click="delete('{{ $table->id }}')"
                            loading="delete('{{ $table->id }}')">
                            Hapus
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-base-content/50">Belum ada data meja.</td>
            </tr>
        @endforelse
    </x-data-table>

    <div>{{ $tables->links() }}</div>
</div>
