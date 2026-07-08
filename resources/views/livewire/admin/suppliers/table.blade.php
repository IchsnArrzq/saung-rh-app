<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-md">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, kode, kontak, telepon...">
                </div>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('suppliers.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Supplier
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontak</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr wire:key="supplier-row-{{ $supplier->id }}">
                        <td>
                            <p class="font-semibold text-stone-800">{{ $supplier->name }}</p>
                            <p class="text-xs text-stone-500">{{ $supplier->code ?: '-' }}</p>
                        </td>
                        <td>{{ $supplier->contact_person ?: '-' }}</td>
                        <td>{{ $supplier->phone ?: '-' }}</td>
                        <td>{{ $supplier->email ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $supplier->is_active ? 'badge-success' : 'badge-ghost' }}">
                                {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    data-confirm="Hapus supplier ini?"
                                    wire:click="delete('{{ $supplier->id }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="delete('{{ $supplier->id }}')">
                                    <span wire:loading.remove wire:target="delete('{{ $supplier->id }}')">Hapus</span>
                                    <span wire:loading wire:target="delete('{{ $supplier->id }}')" class="loading loading-spinner loading-xs"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-stone-500">Belum ada data supplier.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $suppliers->links() }}</div>
</div>
