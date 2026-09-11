<?php

namespace App\Livewire\Admin\Roles;

use App\Domains\System\QueryUseCases\GetRolesQueryUseCase;
use App\Domains\System\UseCases\SaveRoleUseCase;
use App\Models\Role;
use App\Support\FeaturePermissions;
use App\Support\PermissionCatalog;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Tambah peran atau atur hak akses satu peran. Superadmin dan peran yang tidak
 * boleh diubah pengguna ini tampil hanya-baca.
 */
class Form extends Component
{
    public ?Role $role = null;

    public string $name = '';

    /** @var array<int, string> */
    public array $permissions = [];

    /** Peran sumber untuk "Salin hak akses dari" (khusus peran baru). */
    public string $copyFrom = '';

    public ?string $notice = null;

    public function mount(GetRolesQueryUseCase $roles, ?Role $role = null): void
    {
        $this->role = $role?->exists ? $role : null;

        if ($this->role) {
            $this->authorize('view', $this->role);
            $this->name = (string) $this->role->name;
            $this->permissions = $roles->permissionNamesOf($this->role);

            return;
        }

        $this->authorize('create', Role::class);
    }

    public function applyCopy(GetRolesQueryUseCase $roles): void
    {
        $this->authorizeWrite();

        $source = $this->copyFrom !== '' ? $roles->find($this->copyFrom) : null;

        if (! $source) {
            $this->addError('copyFrom', 'Pilih peran yang hak aksesnya mau disalin.');

            return;
        }

        $this->permissions = array_values(array_intersect($roles->permissionNamesOf($source), PermissionCatalog::managedNames()));
        $this->notice = 'Hak akses peran '.$source->label().' disalin. Periksa lagi, lalu simpan.';
    }

    public function toggleRow(string $slug): void
    {
        $this->authorizeWrite();

        $this->togglePermissions(array_map(
            fn (string $ability) => "{$slug}.{$ability}",
            array_keys(PermissionCatalog::ABILITIES),
        ));
    }

    public function toggleGroup(int $index): void
    {
        $this->authorizeWrite();

        $group = PermissionCatalog::modelGroups()[$index] ?? null;

        if (! $group) {
            return;
        }

        $this->togglePermissions(collect($group['rows'])->flatMap(fn (array $row) => array_values($row['permissions']))->all());
    }

    public function save(SaveRoleUseCase $saveRole)
    {
        $this->authorizeWrite();

        $this->validate([
            'name' => $this->role?->isSystem() ? ['nullable'] : ['required', 'string', 'max:50'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ], [
            'name.required' => 'Nama peran wajib diisi.',
            'name.max' => 'Nama peran maksimal 50 karakter.',
        ]);

        try {
            $role = $saveRole->handle($this->role, $this->name, $this->permissions, auth()->user());
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, (string) ($messages[0] ?? ''));
            }

            return null;
        }

        session()->flash('success', $this->role
            ? 'Hak akses peran '.$role->label().' disimpan.'
            : 'Peran '.$role->label().' ditambahkan.');

        return $this->redirectRoute('settings.roles-permissions', navigate: true);
    }

    public function render(GetRolesQueryUseCase $roles): View
    {
        $managed = PermissionCatalog::managedNames();

        return view('livewire.admin.roles.form', [
            'readOnly' => $this->isReadOnly(),
            'features' => FeaturePermissions::ALL,
            'groups' => PermissionCatalog::modelGroups(),
            'abilities' => PermissionCatalog::ABILITIES,
            'selectedCount' => count(array_intersect($this->permissions, $managed)),
            'copyOptions' => $this->role
                ? []
                : $roles->list()
                    ->reject(fn (Role $role) => $role->isLocked())
                    ->mapWithKeys(fn (Role $role) => [(string) $role->id => $role->label()])
                    ->all(),
        ]);
    }

    private function isReadOnly(): bool
    {
        if (! $this->role) {
            return false;
        }

        return $this->role->isLocked() || ! auth()->user()->can('update', $this->role);
    }

    /**
     * Diulang di setiap method tulis: mount() hanya jalan sekali, sedangkan
     * save()/toggleRow() adalah request HTTP tersendiri sesudahnya.
     */
    private function authorizeWrite(): void
    {
        $this->role
            ? $this->authorize('update', $this->role)
            : $this->authorize('create', Role::class);
    }

    /**
     * Semua sudah terpilih → lepas semua; selain itu → pilih semua.
     *
     * @param  array<int, string>  $names
     */
    private function togglePermissions(array $names): void
    {
        $allSelected = array_diff($names, $this->permissions) === [];

        $this->permissions = $allSelected
            ? array_values(array_diff($this->permissions, $names))
            : array_values(array_unique([...$this->permissions, ...$names]));
    }
}
