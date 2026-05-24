<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Manajemen Customer</h2>
            <a href="{{ route('customer-users.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Customer
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white mt-5">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="font-semibold text-stone-800">{{ $customer->name }}</td>
                        <td class="text-stone-600">{{ $customer->email }}</td>
                        <td class="text-center">
                            <form action="{{ route('customer-users.status', $customer) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="badge {{ $customer->is_active ? 'badge-success' : 'badge-error' }} badge-outline hover:opacity-80">
                                    {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('customer-users.edit', $customer) }}" class="btn btn-xs btn-ghost">Edit</a>
                                
                                <form action="{{ route('customer-users.destroy', $customer) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus customer ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error text-white">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-stone-500">Belum ada data customer.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
