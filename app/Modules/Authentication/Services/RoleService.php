<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\DTOs\AssignPermissionsDTO;
use App\Modules\Authentication\DTOs\CreateRoleDTO;
use App\Modules\Authentication\Models\Permission;
use App\Modules\Authentication\Models\Role;
use App\Modules\Authentication\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Collection;

// Lógica de negocio de roles y sus permisos.
class RoleService
{
    public function __construct(private readonly RoleRepository $roles) {}

    public function list(): Collection
    {
        return $this->roles->all();
    }

    // Crea un rol y le asigna permisos si vienen.
    public function create(CreateRoleDTO $data): Role
    {
        $role = $this->roles->create((string) $data->name);
        $this->sync($role, $data->permissions);

        return $role->load('permissions');
    }

    // Actualiza nombre y/o permisos del rol.
    public function update(Role $role, CreateRoleDTO $data): Role
    {
        if ($data->name !== null) {
            $role->update(['name' => $data->name]);
        }

        if ($data->permissions !== []) {
            $this->sync($role, $data->permissions);
        }

        return $role->load('permissions');
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    // Reemplaza los permisos del rol.
    public function syncPermissions(Role $role, AssignPermissionsDTO $data): Role
    {
        $this->sync($role, $data->permissions);

        return $role->load('permissions');
    }

    /**
     * Sincroniza permisos por nombre.
     *
     * @param list<string> $names
     */
    private function sync(Role $role, array $names): void
    {
        $ids = Permission::whereIn('name', $names)->pluck('id');
        $role->permissions()->sync($ids);
    }
}
