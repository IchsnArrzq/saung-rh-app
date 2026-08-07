<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Manajemen Admin & Kasir</h2>
        </div>
    </x-slot>

    @include('admin.partials.flash')

    @if (session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    <div class="flex justify-between">
        <x-button variant="primary" size="sm" icon="ri-add-line" :href="route('admin-users.create')">
            Tambah Akun
        </x-button>
    </div>

    <x-data-table class="mt-5">
        <x-slot:head>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th class="text-center">Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @forelse($users as $user)
            <tr>
                <td class="font-semibold">{{ $user->name }}</td>
                <td class="text-base-content/70">{{ $user->email }}</td>
                <td class="capitalize">
                    <x-badge color="ghost" size="sm" class="font-medium">
                        {{ $user->roles->pluck('name')->join(', ') }}
                    </x-badge>
                </td>
                <td class="text-center">
                    @if (!$user->hasRole('superadmin'))
                        <form action="{{ route('admin-users.status', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <x-button type="submit" variant="{{ $user->is_active ? 'success' : 'error' }}"
                                :outline="true" size="xs">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-button>
                        </form>
                    @else
                        <div class="inline-flex items-center justify-center gap-1.5 font-semibold text-success"
                            title="Akun Superadmin diproteksi oleh sistem">
                            <i class="ri-shield-star-line text-lg"></i>
                            <span>Aktif</span>
                        </div>
                    @endif
                </td>
                <td class="text-right">
                    <div class="inline-flex gap-2">
                        <x-button variant="ghost" size="sm" :href="route('admin-users.edit', $user)">Edit</x-button>

                        @if (!$user->hasRole('superadmin'))
                            <form action="{{ route('admin-users.destroy', $user) }}" method="POST"
                                data-confirm="Yakin ingin menghapus admin ini?" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="error" size="sm" class="text-white">Hapus</x-button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="py-6 text-center text-base-content/50">Belum ada data akun.</td>
            </tr>
        @endforelse
    </x-data-table>
</x-admin-layout>
