<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Authentication\Resources\PermissionResource;
use App\Modules\Authentication\Services\PermissionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// Listado de permisos disponibles.
class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions) {}

    #[OA\Get(
        path: '/api/auth/permissions',
        tags: ['Roles'],
        summary: 'Listar permisos disponibles',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista de permisos')]
    )]
    public function index(): AnonymousResourceCollection
    {
        return PermissionResource::collection($this->permissions->list());
    }
}
