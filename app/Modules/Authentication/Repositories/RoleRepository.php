<?php

namespace App\Modules\Authentication\Repositories;

use App\Modules\Authentication\Models\Role;
use Illuminate\Database\Eloquent\Collection;

// Acceso a datos de roles.
class RoleRepository
{
    public function all(): Collection
    {
        return Role::query()->with('permissions')->orderBy('name')->get();
    }

    public function create(string $name, string $guard = 'api'): Role
    {
        return Role::create(['name' => $name, 'guard_name' => $guard]);
    }
}
