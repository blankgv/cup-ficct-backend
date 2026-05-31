<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreateGrupoDTO;
use App\Modules\AcademicManagement\DTOs\UpdateGrupoDTO;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Requests\StoreGrupoRequest;
use App\Modules\AcademicManagement\Requests\UpdateGrupoRequest;
use App\Modules\AcademicManagement\Resources\GrupoResource;
use App\Modules\AcademicManagement\Services\GrupoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de grupos (paralelos).
class GrupoController extends Controller
{
    public function __construct(private readonly GrupoService $grupos) {}

    #[OA\Get(
        path: '/api/academic-management/grupos',
        tags: ['Grupos'],
        summary: 'Listar grupos',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'gestion', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de grupos')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return GrupoResource::collection(
            $this->grupos->list($request->query('search'), $request->query('gestion'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/academic-management/grupos',
        tags: ['Grupos'],
        summary: 'Crear grupo',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['codigo', 'turno', 'capacidad', 'gestion'],
            properties: [
                new OA\Property(property: 'codigo', type: 'string', example: 'A'),
                new OA\Property(property: 'turno', type: 'string', enum: ['MANANA', 'TARDE', 'NOCHE'], example: 'MANANA'),
                new OA\Property(property: 'capacidad', type: 'integer', maximum: 70, example: 70),
                new OA\Property(property: 'gestion', type: 'string', example: '2026'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Grupo creado', content: new OA\JsonContent(ref: '#/components/schemas/Grupo'))]
    )]
    public function store(StoreGrupoRequest $request): JsonResponse
    {
        $grupo = $this->grupos->create(CreateGrupoDTO::fromArray($request->validated()));

        return (new GrupoResource($grupo))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/grupos/{grupo}',
        tags: ['Grupos'],
        summary: 'Ver grupo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Grupo', content: new OA\JsonContent(ref: '#/components/schemas/Grupo'))]
    )]
    public function show(Grupo $grupo): GrupoResource
    {
        return new GrupoResource($grupo);
    }

    #[OA\Put(
        path: '/api/academic-management/grupos/{grupo}',
        tags: ['Grupos'],
        summary: 'Editar grupo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'codigo', type: 'string'),
                new OA\Property(property: 'turno', type: 'string', enum: ['MANANA', 'TARDE', 'NOCHE']),
                new OA\Property(property: 'capacidad', type: 'integer', maximum: 70),
                new OA\Property(property: 'gestion', type: 'string'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Grupo actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Grupo'))]
    )]
    public function update(UpdateGrupoRequest $request, Grupo $grupo): GrupoResource
    {
        return new GrupoResource($this->grupos->update($grupo, UpdateGrupoDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/grupos/{grupo}',
        tags: ['Grupos'],
        summary: 'Eliminar grupo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Grupo eliminado')]
    )]
    public function destroy(Grupo $grupo): JsonResponse
    {
        $this->grupos->delete($grupo);

        return response()->json(['message' => 'Grupo eliminado.']);
    }
}
