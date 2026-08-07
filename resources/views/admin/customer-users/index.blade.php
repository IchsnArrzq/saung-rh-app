<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Manajemen Customer</h2>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    <div class="flex justify-between">
        <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('customer-users.create')">
            Tambah Customer
        </x-button>
    </div>

    <x-data-table class="mt-5">
        <x-slot:head>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th class="text-center">Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse($customers as $customer)
            <tr>
                <td class="font-semibold">{{ $customer->name }}</td>
                <td class="text-base-content/70">{{ $customer->email }}</td>
                <td class="text-center">
                    <form action="{{ route('customer-users.status', $customer) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <x-button type="submit" variant="{{ $customer->is_active ? 'success' : 'error' }}"
                            :outline="true" size="xs">
                            {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-button>
                    </form>
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="warning" size="sm" :href="route('customer-users.edit', $customer)">
                            Edit
                        </x-button>

                        <form action="{{ route('customer-users.destroy', $customer) }}" method="POST"
                            data-confirm="Yakin ingin menghapus customer ini?" class="inline">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="error" size="sm" class="text-white">Hapus</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-base-content/50">Belum ada data customer.</td>
            </tr>
        @endforelse
    </x-data-table>
</x-admin-layout>
