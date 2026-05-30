<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Collection;

// Lógica de negocio de permisos.
class PermissionService
{
    public function __construct(private readonly PermissionRepository $permissions) {}

    public function list(): Collection
    {
        return $this->permissions->all();
    }
}
