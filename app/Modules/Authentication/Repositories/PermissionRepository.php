<?php

namespace App\Modules\Authentication\Repositories;

use App\Modules\Authentication\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

// Acceso a datos de permisos.
class PermissionRepository
{
    public function all(): Collection
    {
        return Permission::query()->orderBy('name')->get();
    }
}
