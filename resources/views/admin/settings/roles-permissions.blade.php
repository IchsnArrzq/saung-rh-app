@php
    $activeRoleName = request('role', optional($roles->first())->name);
    $activeRole = $roles->firstWhere('name', $activeRoleName) ?? $roles->first();
    $activePermissionNames = $activeRole ? $activeRole->permissions->pluck('name')->all() : [];
    $isSuperadmin = $activeRole && $activeRole->name === 'superadmin';
@endphp

<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">User Role & Permission</h2>
    </x-slot>

    @include('admin.partials.flash')

    <div class="space-y-4 mt-5">
        <div class="tabs tabs-boxed rounded-2xl border border-base-300 bg-base-100 p-1 flex-wrap">
            @foreach ($roles as $role)
                <a role="tab" href="{{ route('settings.roles-permissions', ['role' => $role->name]) }}"
                    class="tab capitalize {{ $activeRole && $activeRole->is($role) ? 'tab-active bg-primary text-primary-content' : 'text-secondary' }}">
                    {{ $role->name }}
                </a>
            @endforeach
        </div>

        @if ($activeRole)
            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                @if ($isSuperadmin)
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        Role Superadmin memiliki seluruh permission secara permanen dan tidak dapat diubah dari sini.
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.roles-permissions.update', $activeRole) }}">
                    @csrf
                    @method('PATCH')

                    <div class="overflow-x-auto rounded-2xl border border-stone-200">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Model</th>
                                    @foreach (\App\Support\PolicyPermissions::$abilities as $ability)
                                        <th class="text-center">{{ $ability }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissionGroups as $group)
                                    <tr>
                                        <td class="font-semibold text-stone-800">
                                            <div class="flex items-center gap-2">
                                                <span>{{ $group['label'] }}</span>
                                                @unless ($isSuperadmin)
                                                    <x-button variant="ghost" size="xs" class="text-base-content/60"
                                                        data-select-all="{{ $group['slug'] }}">Semua</x-button>
                                                @endunless
                                            </div>
                                        </td>
                                        @foreach ($group['abilities'] as $ability)
                                            @php($permissionName = $group['slug'].'.'.$ability)
                                            <td class="text-center">
                                                <input type="checkbox" class="checkbox checkbox-sm"
                                                    data-group="{{ $group['slug'] }}" name="permissions[]"
                                                    value="{{ $permissionName }}"
                                                    @checked(in_array($permissionName, $activePermissionNames))
                                                    @disabled($isSuperadmin)>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @unless ($isSuperadmin)
                        <div class="pt-4">
                            <x-button type="submit" variant="primary">Simpan Permission</x-button>
                        </div>
                    @endunless
                </form>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-select-all]');
            if (!trigger) return;

            const group = trigger.getAttribute('data-select-all');
            const boxes = document.querySelectorAll('input[data-group="' + group + '"]');
            const shouldCheck = !Array.from(boxes).every(box => box.checked);

            boxes.forEach(box => {
                if (!box.disabled) box.checked = shouldCheck;
            });
        });
    </script>
</x-admin-layout>
