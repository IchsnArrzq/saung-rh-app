<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold">Manajemen Admin</h2>
            <a href="{{ route('admin-users.create') }}" class="btn btn-sm bg-emerald-800 text-amber-50 hover:bg-emerald-700">
                <i class="ri-add-line"></i>
                Tambah Admin
            </a>
        </div>
    </x-slot>

    @include('admin.partials.flash')
    
    @if(session('error'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white mt-5">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="font-semibold text-stone-800">{{ $user->name }}</td>
                        <td class="text-stone-600">{{ $user->email }}</td>
                        <td class="capitalize">
                            <span class="badge badge-ghost badge-sm font-medium text-stone-600">{{ $user->roles->pluck('name')->join(', ') }}</span>
                        </td>
                        <td class="text-center">
                            @if(!$user->hasRole('superadmin'))
                                <form action="{{ route('admin-users.status', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="badge {{ $user->is_active ? 'badge-success' : 'badge-error' }} badge-outline hover:opacity-80 transition-opacity">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            @else
                                <div class="inline-flex items-center justify-center gap-1.5 font-semibold text-success" title="Akun Superadmin diproteksi oleh sistem">
                                    <i class="ri-shield-star-line text-lg"></i>
                                    <span>Aktif</span>
                                </div>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('admin-users.edit', $user) }}" class="btn btn-sm btn-ghost text-stone-600">Edit</a>
                                
                                @if(!$user->hasRole('superadmin'))
                                    <form action="{{ route('admin-users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus admin ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-error text-white">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-stone-500 py-6">Belum ada data admin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
