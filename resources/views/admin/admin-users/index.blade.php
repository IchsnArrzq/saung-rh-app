<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Karyawan</h2>
    </x-slot>

    @php
        $filtered = $search !== '' || $role !== '';
        $actor = auth()->user();
        $actorIsSuperadmin = $actor->hasRole(\App\Models\Role::LOCKED);
    @endphp

    <div class="space-y-5">
        @include('admin.partials.flash')

        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin-users.index') }}" role="search"
                class="flex flex-1 flex-wrap items-center gap-2">
                <x-search-input name="q" value="{{ $search }}" placeholder="Cari nama, email, atau nomor HP"
                    label="Cari karyawan" class="sm:max-w-xs" />

                <div class="w-full sm:w-52">
                    <x-select :bare="true" name="peran" label="Saring menurut peran" :options="$roleOptions"
                        :selected="$role" placeholder="Semua peran" class="w-full" />
                </div>

                <x-button type="submit" variant="outline" icon="ri-filter-3-line">Terapkan</x-button>

                @if ($filtered)
                    <x-button variant="ghost" :href="route('admin-users.index')">Reset</x-button>
                @endif
            </form>

            @can('create', \App\Models\User::class)
                <x-button variant="primary" icon="ri-user-add-line" :href="route('admin-users.create')">
                    Tambah karyawan
                </x-button>
            @endcan
        </div>

        @if ($users->isEmpty())
            <x-empty-state icon="ri-team-line"
                :title="$filtered ? 'Tidak ada karyawan yang cocok' : 'Belum ada akun karyawan'"
                :description="$filtered
                    ? 'Coba kata kunci atau peran lain.'
                    : 'Tambahkan akun untuk kasir, pelayan, resepsionis, dan staf lainnya.'">
                <x-slot:actions>
                    @if ($filtered)
                        <x-button variant="outline" :href="route('admin-users.index')">Tampilkan semua karyawan</x-button>
                    @else
                        @can('create', \App\Models\User::class)
                            <x-button icon="ri-user-add-line" :href="route('admin-users.create')">Tambah karyawan pertama</x-button>
                        @endcan
                    @endif
                </x-slot:actions>
            </x-empty-state>
        @else
            <x-data-table>
                <x-slot:head>
                    <tr>
                        <th>Karyawan</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th class="w-px text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($users as $user)
                    @php
                        $isSuperadmin = $user->hasRole(\App\Models\Role::LOCKED);
                        $isSelf = $user->is($actor);
                        $canEdit = $actor->can('update', $user) && (! $isSuperadmin || $actorIsSuperadmin);
                        $toggleIcon = $user->is_active ? 'ri-user-unfollow-line' : 'ri-user-follow-line';
                        $toggleLabel = ($user->is_active ? 'Nonaktifkan akun ' : 'Aktifkan akun ').$user->name;
                    @endphp

                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :user="$user" size="sm" />
                                <div class="min-w-0">
                                    <p class="truncate font-medium">
                                        {{ $user->name }}
                                        @if ($isSelf)
                                            <span class="font-normal text-base-content/60">(Anda)</span>
                                        @endif
                                    </p>
                                    <p class="truncate text-sm text-base-content/60">
                                        {{ $user->email }}@if ($user->phone) · {{ $user->phone }}@endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $userRole)
                                    @if ($userRole->name === \App\Models\Role::LOCKED)
                                        <x-badge color="warning" size="sm" icon="ri-shield-star-line">Superadmin</x-badge>
                                    @else
                                        <x-badge color="ghost" size="sm">{{ \App\Models\Role::labelFor($userRole->name) }}</x-badge>
                                    @endif
                                @empty
                                    <span class="text-sm text-base-content/60">Belum ada peran</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <x-badge size="sm" :color="$user->is_active ? 'success' : 'ghost'">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </td>
                        <td>
                            <div class="flex justify-end gap-1">
                                @if ($canEdit)
                                    <x-button variant="ghost" size="sm" shape="square" icon="ri-pencil-line"
                                        label="Ubah akun {{ $user->name }}" :href="route('admin-users.edit', $user)" />
                                @endif

                                @if (! $isSuperadmin && ! $isSelf)
                                    @can('update', $user)
                                        <form method="POST" action="{{ route('admin-users.status', $user) }}"
                                            data-confirm="{{ $user->is_active
                                                ? 'Nonaktifkan akun '.$user->name.'? Ia tidak bisa masuk sampai diaktifkan lagi.'
                                                : 'Aktifkan lagi akun '.$user->name.'?' }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-button type="submit" variant="ghost" size="sm" shape="square"
                                                :label="$toggleLabel" :icon="$toggleIcon" />
                                        </form>
                                    @endcan

                                    @can('delete', $user)
                                        <form method="POST" action="{{ route('admin-users.destroy', $user) }}"
                                            data-confirm="Hapus akun {{ $user->name }}? Akun yang masih tercatat di pesanan, shift, atau tip tidak bisa dihapus — nonaktifkan saja.">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="error" outline size="sm" shape="square"
                                                icon="ri-delete-bin-line" label="Hapus akun {{ $user->name }}" />
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>

            {{ $users->links() }}
        @endif
    </div>
</x-admin-layout>
