<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreatePeriodoDTO;
use App\Modules\AcademicManagement\DTOs\UpdatePeriodoDTO;
use App\Modules\AcademicManagement\Models\Periodo;
use App\Modules\AcademicManagement\Requests\StorePeriodoRequest;
use App\Modules\AcademicManagement\Requests\UpdatePeriodoRequest;
use App\Modules\AcademicManagement\Resources\PeriodoResource;
use App\Modules\AcademicManagement\Services\PeriodoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de periodos de clases.
class PeriodoController extends Controller
{
    public function __construct(private readonly PeriodoService $periodos) {}

    #[OA\Get(
        path: '/api/academic-management/periodos',
        tags: ['Periodos'],
        summary: 'Listar periodos',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de periodos')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return PeriodoResource::collection(
            $this->periodos->list($request->query('search'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/academic-management/periodos',
        tags: ['Periodos'],
        summary: 'Crear periodo de clases',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['codigo', 'gestion', 'fecha_inicio_clases', 'fecha_fin_clases'],
            properties: [
                new OA\Property(property: 'codigo', type: 'string', example: '1/2026'),
                new OA\Property(property: 'gestion', type: 'string', example: '2026'),
                new OA\Property(property: 'fecha_inicio_clases', type: 'string', format: 'date', example: '2026-03-01'),
                new OA\Property(property: 'fecha_fin_clases', type: 'string', format: 'date', example: '2026-06-30'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Periodo creado')]
    )]
    public function store(StorePeriodoRequest $request): JsonResponse
    {
        $periodo = $this->periodos->create(CreatePeriodoDTO::fromArray($request->validated()));

        return (new PeriodoResource($periodo))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/periodos/{periodo}',
        tags: ['Periodos'],
        summary: 'Ver periodo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'periodo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Periodo')]
    )]
    public function show(Periodo $periodo): PeriodoResource
    {
        return new PeriodoResource($periodo);
    }

    #[OA\Put(
        path: '/api/academic-management/periodos/{periodo}',
        tags: ['Periodos'],
        summary: 'Editar periodo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'periodo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'codigo', type: 'string'),
                new OA\Property(property: 'gestion', type: 'string'),
                new OA\Property(property: 'fecha_inicio_clases', type: 'string', format: 'date'),
                new OA\Property(property: 'fecha_fin_clases', type: 'string', format: 'date'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Periodo actualizado')]
    )]
    public function update(UpdatePeriodoRequest $request, Periodo $periodo): PeriodoResource
    {
        return new PeriodoResource($this->periodos->update($periodo, UpdatePeriodoDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/periodos/{periodo}',
        tags: ['Periodos'],
        summary: 'Eliminar periodo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'periodo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Periodo eliminado')]
    )]
    public function destroy(Periodo $periodo): JsonResponse
    {
        $this->periodos->delete($periodo);

        return response()->json(['message' => 'Periodo eliminado.']);
    }
}
