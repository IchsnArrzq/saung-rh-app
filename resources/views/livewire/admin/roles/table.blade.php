<div class="space-y-5">
    @include('admin.partials.flash')

    <div class="flex flex-wrap items-start justify-between gap-3">
        <p class="max-w-2xl text-sm text-base-content/70">
            Peran menentukan halaman dan data yang boleh dibuka setiap akun. Peran bawaan dipakai sistem, jadi namanya
            tetap — hak aksesnya tetap bisa diatur. Superadmin selalu memiliki semua akses.
        </p>

        @can('create', \App\Models\Role::class)
            <x-button variant="primary" icon="ri-add-line" :href="route('settings.roles-permissions.create')" wire:navigate>
                Tambah peran
            </x-button>
        @endcan
    </div>

    <x-data-table>
        <x-slot:head>
            <tr>
                <th>Peran</th>
                <th class="text-right">Pengguna</th>
                <th class="text-right">Hak akses</th>
                <th class="w-px text-right">Aksi</th>
            </tr>
        </x-slot:head>

        @foreach ($roles as $role)
            <tr wire:key="role-{{ $role->id }}">
                <td>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-medium">{{ $role->label() }}</span>
                        @if ($role->isLocked())
                            <x-badge color="warning" size="sm" icon="ri-lock-line">Terkunci</x-badge>
                        @elseif ($role->isSystem())
                            <x-badge color="ghost" size="sm">Bawaan</x-badge>
                        @endif
                    </div>
                </td>
                <td class="text-right tabular-nums">{{ $role->users_count }}</td>
                <td class="text-right tabular-nums">{{ $role->isLocked() ? 'Semua' : $role->permissions_count }}</td>
                <td>
                    <div class="flex justify-end gap-1">
                        @if (! $role->isLocked() && auth()->user()->can('update', $role))
                            <x-button variant="ghost" size="sm" shape="square" icon="ri-pencil-line"
                                label="Ubah peran {{ $role->label() }}"
                                :href="route('settings.roles-permissions.edit', $role)" wire:navigate />
                        @else
                            @can('view', $role)
                                <x-button variant="ghost" size="sm" shape="square" icon="ri-eye-line"
                                    label="Lihat peran {{ $role->label() }}"
                                    :href="route('settings.roles-permissions.edit', $role)" wire:navigate />
                            @endcan
                        @endif

                        @if (! $role->isSystem())
                            @can('delete', $role)
                                <x-button variant="error" outline size="sm" shape="square" icon="ri-delete-bin-line"
                                    label="Hapus peran {{ $role->label() }}"
                                    wire:click="delete('{{ $role->id }}')" loading="delete('{{ $role->id }}')"
                                    data-confirm="Hapus peran {{ $role->label() }}? Peran yang masih dipakai akun tidak bisa dihapus." />
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</div>
