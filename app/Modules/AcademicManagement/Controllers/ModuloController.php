<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreateModuloDTO;
use App\Modules\AcademicManagement\DTOs\UpdateModuloDTO;
use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\AcademicManagement\Requests\StoreModuloRequest;
use App\Modules\AcademicManagement\Requests\UpdateModuloRequest;
use App\Modules\AcademicManagement\Resources\ModuloResource;
use App\Modules\AcademicManagement\Services\ModuloService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de módulos (edificios).
class ModuloController extends Controller
{
    public function __construct(private readonly ModuloService $modulos) {}

    #[OA\Get(
        path: '/api/academic-management/modulos',
        tags: ['Modulos'],
        summary: 'Listar módulos',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de módulos'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return ModuloResource::collection(
            $this->modulos->list($request->query('search'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/academic-management/modulos',
        tags: ['Modulos'],
        summary: 'Crear módulo',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['numero', 'nombre'],
            properties: [
                new OA\Property(property: 'numero', type: 'string', example: '236'),
                new OA\Property(property: 'nombre', type: 'string', example: 'Módulo 236'),
                new OA\Property(property: 'ubicacion', type: 'string', example: 'Campus central'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Módulo creado', content: new OA\JsonContent(ref: '#/components/schemas/Modulo'))]
    )]
    public function store(StoreModuloRequest $request): JsonResponse
    {
        $modulo = $this->modulos->create(CreateModuloDTO::fromArray($request->validated()));

        return (new ModuloResource($modulo))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/modulos/{modulo}',
        tags: ['Modulos'],
        summary: 'Ver módulo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'modulo', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Módulo', content: new OA\JsonContent(ref: '#/components/schemas/Modulo'))]
    )]
    public function show(Modulo $modulo): ModuloResource
    {
        return new ModuloResource($modulo);
    }

    #[OA\Put(
        path: '/api/academic-management/modulos/{modulo}',
        tags: ['Modulos'],
        summary: 'Editar módulo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'modulo', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'numero', type: 'string'),
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'ubicacion', type: 'string'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Módulo actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Modulo'))]
    )]
    public function update(UpdateModuloRequest $request, Modulo $modulo): ModuloResource
    {
        return new ModuloResource($this->modulos->update($modulo, UpdateModuloDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/modulos/{modulo}',
        tags: ['Modulos'],
        summary: 'Eliminar módulo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'modulo', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Módulo eliminado')]
    )]
    public function destroy(Modulo $modulo): JsonResponse
    {
        $this->modulos->delete($modulo);

        return response()->json(['message' => 'Módulo eliminado.']);
    }
}
