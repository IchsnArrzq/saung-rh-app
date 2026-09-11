<?php

namespace App\Livewire\Admin\Roles;

use App\Domains\System\QueryUseCases\GetRolesQueryUseCase;
use App\Domains\System\UseCases\DeleteRoleUseCase;
use App\Models\Role;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Table extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', Role::class);
    }

    public function delete(string $id, GetRolesQueryUseCase $roles, DeleteRoleUseCase $deleteRole): void
    {
        $role = $roles->find($id);

        if (! $role) {
            session()->flash('error', 'Peran tidak ditemukan. Mungkin sudah dihapus — muat ulang halaman.');

            return;
        }

        $this->authorize('delete', $role);

        try {
            $deleteRole->handle($role, auth()->user());
        } catch (ValidationException $e) {
            session()->flash('error', (string) collect($e->errors())->flatten()->first());

            return;
        }

        session()->flash('success', 'Peran "'.$role->label().'" dihapus.');
    }

    public function render(GetRolesQueryUseCase $roles): View
    {
        return view('livewire.admin.roles.table', [
            'roles' => $roles->list(),
        ]);
    }
}
