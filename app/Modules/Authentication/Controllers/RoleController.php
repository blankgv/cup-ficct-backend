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
use OpenApi\Attributes as OA;

// CRUD de roles + asignación de permisos.
class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    #[OA\Get(
        path: '/api/auth/roles',
        tags: ['Roles'],
        summary: 'Listar roles',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista de roles')]
    )]
    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection($this->roles->list());
    }

    #[OA\Post(
        path: '/api/auth/roles',
        tags: ['Roles'],
        summary: 'Crear rol',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'AUXILIAR'),
                new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Rol creado', content: new OA\JsonContent(ref: '#/components/schemas/Role'))]
    )]
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roles->create(CreateRoleDTO::fromArray($request->validated()));

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/auth/roles/{role}',
        tags: ['Roles'],
        summary: 'Ver rol',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Rol', content: new OA\JsonContent(ref: '#/components/schemas/Role'))]
    )]
    public function show(Role $role): RoleResource
    {
        return new RoleResource($role->load('permissions'));
    }

    #[OA\Put(
        path: '/api/auth/roles/{role}',
        tags: ['Roles'],
        summary: 'Editar rol',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Rol actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Role'))]
    )]
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        return new RoleResource($this->roles->update($role, CreateRoleDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/auth/roles/{role}',
        tags: ['Roles'],
        summary: 'Eliminar rol',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Rol eliminado')]
    )]
    public function destroy(Role $role): JsonResponse
    {
        $this->roles->delete($role);

        return response()->json(['message' => 'Rol eliminado.']);
    }

    #[OA\Put(
        path: '/api/auth/roles/{role}/permissions',
        tags: ['Roles'],
        summary: 'Sincronizar permisos de un rol',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['permissions'],
            properties: [new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string'))]
        )),
        responses: [new OA\Response(response: 200, description: 'Permisos actualizados', content: new OA\JsonContent(ref: '#/components/schemas/Role'))]
    )]
    public function syncPermissions(AssignPermissionsRequest $request, Role $role): RoleResource
    {
        return new RoleResource($this->roles->syncPermissions($role, AssignPermissionsDTO::fromArray($request->validated())));
    }
}
