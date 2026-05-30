<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\DTOs\AssignPermissionsDTO;
use App\Modules\Authentication\DTOs\CreateRoleDTO;
use App\Modules\Authentication\Models\Role;
use App\Modules\Authentication\Requests\AssignPermissionsRequest;
use App\Modules\Authentication\Requests\StoreRoleRequest;
use App\Modules\Authentication\Requests\UpdateRoleRequest;
use App\Modules\Authentication\Resources\RoleResource;
use App\Modules\Authentication\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// CRUD de roles + asignación de permisos.
class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    // GET /api/auth/roles
    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection($this->roles->list());
    }

    // POST /api/auth/roles
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roles->create(CreateRoleDTO::fromArray($request->validated()));

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    // GET /api/auth/roles/{role}
    public function show(Role $role): RoleResource
    {
        return new RoleResource($role->load('permissions'));
    }

    // PUT /api/auth/roles/{role}
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        return new RoleResource($this->roles->update($role, CreateRoleDTO::fromArray($request->validated())));
    }

    // DELETE /api/auth/roles/{role}
    public function destroy(Role $role): JsonResponse
    {
        $this->roles->delete($role);

        return response()->json(['message' => 'Rol eliminado.']);
    }

    // PUT /api/auth/roles/{role}/permissions
    public function syncPermissions(AssignPermissionsRequest $request, Role $role): RoleResource
    {
        return new RoleResource($this->roles->syncPermissions($role, AssignPermissionsDTO::fromArray($request->validated())));
    }
}
