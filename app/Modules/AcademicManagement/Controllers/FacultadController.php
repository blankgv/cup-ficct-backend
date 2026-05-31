<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreateFacultadDTO;
use App\Modules\AcademicManagement\DTOs\UpdateFacultadDTO;
use App\Modules\AcademicManagement\Models\Facultad;
use App\Modules\AcademicManagement\Requests\StoreFacultadRequest;
use App\Modules\AcademicManagement\Requests\UpdateFacultadRequest;
use App\Modules\AcademicManagement\Resources\FacultadResource;
use App\Modules\AcademicManagement\Services\FacultadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de facultades.
class FacultadController extends Controller
{
    public function __construct(private readonly FacultadService $facultades) {}

    #[OA\Get(
        path: '/api/academic-management/facultades',
        tags: ['Facultades'],
        summary: 'Listar facultades',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de facultades')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return FacultadResource::collection(
            $this->facultades->list($request->query('search'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/academic-management/facultades',
        tags: ['Facultades'],
        summary: 'Crear facultad',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['codigo', 'nombre', 'abreviatura'],
            properties: [
                new OA\Property(property: 'codigo', type: 'string', example: '187'),
                new OA\Property(property: 'nombre', type: 'string', example: 'Ingeniería en Ciencias de la Computación y Telecomunicaciones'),
                new OA\Property(property: 'abreviatura', type: 'string', example: 'FICCT'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Facultad creada', content: new OA\JsonContent(ref: '#/components/schemas/Facultad'))]
    )]
    public function store(StoreFacultadRequest $request): JsonResponse
    {
        $facultad = $this->facultades->create(CreateFacultadDTO::fromArray($request->validated()));

        return (new FacultadResource($facultad))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/facultades/{facultad}',
        tags: ['Facultades'],
        summary: 'Ver facultad',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'facultad', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Facultad', content: new OA\JsonContent(ref: '#/components/schemas/Facultad'))]
    )]
    public function show(Facultad $facultad): FacultadResource
    {
        return new FacultadResource($facultad);
    }

    #[OA\Put(
        path: '/api/academic-management/facultades/{facultad}',
        tags: ['Facultades'],
        summary: 'Editar facultad',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'facultad', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'codigo', type: 'string'),
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'abreviatura', type: 'string'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Facultad actualizada', content: new OA\JsonContent(ref: '#/components/schemas/Facultad'))]
    )]
    public function update(UpdateFacultadRequest $request, Facultad $facultad): FacultadResource
    {
        return new FacultadResource($this->facultades->update($facultad, UpdateFacultadDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/facultades/{facultad}',
        tags: ['Facultades'],
        summary: 'Eliminar facultad',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'facultad', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Facultad eliminada')]
    )]
    public function destroy(Facultad $facultad): JsonResponse
    {
        $this->facultades->delete($facultad);

        return response()->json(['message' => 'Facultad eliminada.']);
    }
}
