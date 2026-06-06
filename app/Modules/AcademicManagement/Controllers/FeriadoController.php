<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\Models\Feriado;
use App\Modules\AcademicManagement\Requests\ImportFeriadosRequest;
use App\Modules\AcademicManagement\Requests\StoreFeriadoRequest;
use App\Modules\AcademicManagement\Requests\UpdateFeriadoRequest;
use App\Modules\AcademicManagement\Resources\FeriadoResource;
use App\Modules\AcademicManagement\Services\FeriadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de feriados + importación desde API.
class FeriadoController extends Controller
{
    public function __construct(private readonly FeriadoService $feriados) {}

    #[OA\Get(
        path: '/api/academic-management/feriados',
        tags: ['Feriados'],
        summary: 'Listar feriados',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'gestion', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de feriados')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return FeriadoResource::collection(
            $this->feriados->list($request->query('gestion'), (int) $request->query('per_page', 50))
        );
    }

    #[OA\Post(
        path: '/api/academic-management/feriados/importar',
        tags: ['Feriados'],
        summary: 'Importar feriados de una gestión desde la API',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['gestion'],
            properties: [new OA\Property(property: 'gestion', type: 'string', example: '2026')]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Resumen {importados}'),
            new OA\Response(response: 422, description: 'API no configurada'),
        ]
    )]
    public function importar(ImportFeriadosRequest $request): JsonResponse
    {
        return response()->json($this->feriados->importar($request->validated()['gestion']));
    }

    #[OA\Post(
        path: '/api/academic-management/feriados',
        tags: ['Feriados'],
        summary: 'Crear feriado',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['fecha', 'descripcion', 'gestion'],
            properties: [
                new OA\Property(property: 'fecha', type: 'string', format: 'date', example: '2026-05-01'),
                new OA\Property(property: 'descripcion', type: 'string', example: 'Día del Trabajo'),
                new OA\Property(property: 'gestion', type: 'string', example: '2026'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Feriado creado')]
    )]
    public function store(StoreFeriadoRequest $request): JsonResponse
    {
        return (new FeriadoResource($this->feriados->create($request->validated())))->response()->setStatusCode(201);
    }

    #[OA\Put(
        path: '/api/academic-management/feriados/{feriado}',
        tags: ['Feriados'],
        summary: 'Editar feriado',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'feriado', in: 'path', required: true, description: 'Fecha (Y-m-d)', schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'descripcion', type: 'string'),
                new OA\Property(property: 'gestion', type: 'string'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Feriado actualizado')]
    )]
    public function update(UpdateFeriadoRequest $request, Feriado $feriado): FeriadoResource
    {
        return new FeriadoResource($this->feriados->update($feriado, $request->validated()));
    }

    #[OA\Delete(
        path: '/api/academic-management/feriados/{feriado}',
        tags: ['Feriados'],
        summary: 'Eliminar feriado',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'feriado', in: 'path', required: true, description: 'Fecha (Y-m-d)', schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Feriado eliminado')]
    )]
    public function destroy(Feriado $feriado): JsonResponse
    {
        $this->feriados->delete($feriado);

        return response()->json(['message' => 'Feriado eliminado.']);
    }
}
