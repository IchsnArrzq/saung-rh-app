<div class="space-y-5">
    @include('admin.partials.flash')

    <section class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full max-w-md">
                    <i class="ri-search-line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400"></i>
                    <input type="text" class="input input-bordered w-full pl-10" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama, kode, telepon, email...">
                </div>
                @if ($search !== '')
                    <button type="button" class="btn btn-sm btn-ghost" wire:click="$set('search', '')">Reset</button>
                @endif
            </div>

            <a href="{{ route('customers.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Pelanggan
            </a>
        </div>
    </section>

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr wire:key="customer-row-{{ $customer->id }}">
                        <td>
                            <p class="font-semibold text-stone-800">{{ $customer->name }}</p>
                            <p class="text-xs text-stone-500">{{ $customer->code ?: '-' }}</p>
                        </td>
                        <td>{{ $customer->phone ?: '-' }}</td>
                        <td>{{ $customer->email ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $customer->is_active ? 'badge-success' : 'badge-ghost' }}">
                                {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-error text-white"
                                    data-confirm="Hapus pelanggan ini?"
                                    wire:click="delete('{{ $customer->id }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="delete('{{ $customer->id }}')">
                                    <span wire:loading.remove wire:target="delete('{{ $customer->id }}')">Hapus</span>
                                    <span wire:loading wire:target="delete('{{ $customer->id }}')" class="loading loading-spinner loading-xs"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-stone-500">Belum ada data pelanggan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $customers->links() }}</div>
</div>
