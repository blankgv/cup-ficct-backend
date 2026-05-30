<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Resources\PermissionResource;
use App\Modules\Authentication\Services\PermissionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Listado de permisos disponibles.
class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions) {}

    // GET /api/auth/permissions
    public function index(): AnonymousResourceCollection
    {
        return PermissionResource::collection($this->permissions->list());
    }
}
