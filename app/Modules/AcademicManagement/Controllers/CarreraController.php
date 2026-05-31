<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreateCarreraDTO;
use App\Modules\AcademicManagement\DTOs\UpdateCarreraDTO;
use App\Modules\AcademicManagement\Models\Carrera;
use App\Modules\AcademicManagement\Requests\StoreCarreraRequest;
use App\Modules\AcademicManagement\Requests\UpdateCarreraRequest;
use App\Modules\AcademicManagement\Resources\CarreraResource;
use App\Modules\AcademicManagement\Services\CarreraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de carreras.
class CarreraController extends Controller
{
    public function __construct(private readonly CarreraService $carreras) {}

    #[OA\Get(
        path: '/api/academic-management/carreras',
        tags: ['Carreras'],
        summary: 'Listar carreras',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'facultad', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de carreras')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return CarreraResource::collection(
            $this->carreras->list($request->query('search'), $request->query('facultad'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/academic-management/carreras',
        tags: ['Carreras'],
        summary: 'Crear carrera',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['codigo', 'nombre', 'facultad_codigo'],
            properties: [
                new OA\Property(property: 'codigo', type: 'string', example: '187-09'),
                new OA\Property(property: 'nombre', type: 'string', example: 'Ingeniería de Sistemas'),
                new OA\Property(property: 'facultad_codigo', type: 'string', example: '187'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Carrera creada', content: new OA\JsonContent(ref: '#/components/schemas/Carrera')),
            new OA\Response(response: 422, description: 'Código no coincide con la facultad'),
        ]
    )]
    public function store(StoreCarreraRequest $request): JsonResponse
    {
        $carrera = $this->carreras->create(CreateCarreraDTO::fromArray($request->validated()));

        return (new CarreraResource($carrera))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/carreras/{carrera}',
        tags: ['Carreras'],
        summary: 'Ver carrera',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'carrera', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Carrera', content: new OA\JsonContent(ref: '#/components/schemas/Carrera'))]
    )]
    public function show(Carrera $carrera): CarreraResource
    {
        return new CarreraResource($carrera);
    }

    #[OA\Put(
        path: '/api/academic-management/carreras/{carrera}',
        tags: ['Carreras'],
        summary: 'Editar carrera',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'carrera', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'codigo', type: 'string'),
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'facultad_codigo', type: 'string'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Carrera actualizada', content: new OA\JsonContent(ref: '#/components/schemas/Carrera'))]
    )]
    public function update(UpdateCarreraRequest $request, Carrera $carrera): CarreraResource
    {
        return new CarreraResource($this->carreras->update($carrera, UpdateCarreraDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/carreras/{carrera}',
        tags: ['Carreras'],
        summary: 'Eliminar carrera',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'carrera', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Carrera eliminada')]
    )]
    public function destroy(Carrera $carrera): JsonResponse
    {
        $this->carreras->delete($carrera);

        return response()->json(['message' => 'Carrera eliminada.']);
    }
}
