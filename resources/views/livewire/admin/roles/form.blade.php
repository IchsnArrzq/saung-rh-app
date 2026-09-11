<form wire:submit="save" class="space-y-6">
    @include('admin.partials.flash')

    @if ($notice)
        <x-alert type="info" dismissible>{{ $notice }}</x-alert>
    @endif

    @if ($role?->isLocked())
        <x-alert type="warning" icon="ri-lock-line" title="Peran Superadmin terkunci">
            Superadmin selalu memiliki semua akses — termasuk halaman dan data yang baru ditambahkan nanti — jadi hak
            aksesnya tidak diatur di sini, dan perannya tidak bisa diubah atau dihapus.
        </x-alert>

        <div class="flex justify-end">
            <x-button variant="ghost" :href="route('settings.roles-permissions')" wire:navigate>Kembali ke daftar peran</x-button>
        </div>
    @else
        @if ($readOnly)
            <x-alert type="info" icon="ri-eye-line">
                Anda bisa melihat hak akses peran ini, tapi tidak mengubahnya.
            </x-alert>
        @endif

        <x-card title="Identitas peran">
            <div class="grid gap-4 md:grid-cols-2">
                @if ($role?->isSystem())
                    <div>
                        <p class="text-sm text-base-content/70">Nama peran</p>
                        <p class="mt-1 flex items-center gap-2 font-medium">
                            {{ $role->label() }}
                            <x-badge color="ghost" size="sm">Bawaan</x-badge>
                        </p>
                        <p class="mt-1 text-xs text-base-content/60">Nama peran bawaan dipakai sistem sehingga tidak bisa diganti.</p>
                    </div>
                @else
                    <x-input label="Nama peran" name="name" wire:model="name" maxlength="50" required
                        :disabled="$readOnly" placeholder="Mis. Barista, Kapten, Supervisor" />
                @endif

                @if (! $role && $copyOptions !== [])
                    <div class="space-y-2">
                        <x-select label="Salin hak akses dari" name="copyFrom" wire:model="copyFrom" :options="$copyOptions"
                            placeholder="Pilih peran" hint="Opsional — titik awal yang masih bisa diubah." />
                        <x-button variant="outline" size="sm" icon="ri-file-copy-line" wire:click="applyCopy" loading="applyCopy">
                            Salin hak akses
                        </x-button>
                    </div>
                @endif
            </div>
        </x-card>

        <x-card title="Akses halaman" description="Portal dan halaman yang boleh dibuka peran ini.">
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($features as $permission => $feature)
                    <div wire:key="feature-{{ $permission }}" class="rounded-lg bg-base-200 p-3">
                        <x-checkbox :label="$feature['label']" :hint="$feature['description']" value="{{ $permission }}"
                            wire:model="permissions" :disabled="$readOnly" size="sm" />
                    </div>
                @endforeach
            </div>
        </x-card>

        <section class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold">Hak akses data</h2>
                    <p class="text-sm text-base-content/70">
                        Apa yang boleh dilakukan pada setiap jenis data. Tanpa "Lihat daftar", halamannya tidak bisa dibuka.
                    </p>
                </div>
                <p class="text-sm text-base-content/70">
                    <span class="font-semibold tabular-nums text-base-content">{{ $selectedCount }}</span> hak akses dipilih
                </p>
            </div>

            @error('permissions')
                <x-alert type="error">{{ $message }}</x-alert>
            @enderror

            @foreach ($groups as $groupIndex => $group)
                <div wire:key="permission-group-{{ $groupIndex }}" class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold">{{ $group['label'] }}</h3>
                        @unless ($readOnly)
                            <x-button variant="ghost" size="sm" wire:click="toggleGroup({{ $groupIndex }})"
                                loading="toggleGroup({{ $groupIndex }})">
                                Pilih atau lepas semua
                            </x-button>
                        @endunless
                    </div>

                    <x-data-table size="sm">
                        <x-slot:head>
                            <tr>
                                <th>Data</th>
                                @foreach ($abilities as $abilityLabel)
                                    <th class="text-center">{{ $abilityLabel }}</th>
                                @endforeach
                                <th class="w-px"><span class="sr-only">Semua kemampuan</span></th>
                            </tr>
                        </x-slot:head>

                        @foreach ($group['rows'] as $row)
                            <tr wire:key="permission-row-{{ $row['slug'] }}">
                                <th scope="row" class="font-medium">{{ $row['label'] }}</th>
                                @foreach ($row['permissions'] as $ability => $permission)
                                    <td class="text-center">
                                        <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                                            value="{{ $permission }}" wire:model="permissions"
                                            aria-label="{{ $abilities[$ability] }}: {{ $row['label'] }}" @disabled($readOnly)>
                                    </td>
                                @endforeach
                                <td>
                                    @unless ($readOnly)
                                        <x-button variant="ghost" size="xs" wire:click="toggleRow('{{ $row['slug'] }}')"
                                            loading="toggleRow('{{ $row['slug'] }}')" label="Pilih atau lepas semua untuk {{ $row['label'] }}">
                                            Semua
                                        </x-button>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </x-data-table>
                </div>
            @endforeach
        </section>

        @if ($readOnly)
            <div class="flex justify-end">
                <x-button variant="ghost" :href="route('settings.roles-permissions')" wire:navigate>Kembali ke daftar peran</x-button>
            </div>
        @else
            <x-form-actions :cancel-href="route('settings.roles-permissions')" loading="save"
                :submit-label="$role ? 'Simpan hak akses' : 'Tambah peran'" />
        @endif
    @endif
</form>
