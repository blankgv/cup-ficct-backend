<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreateAulaDTO;
use App\Modules\AcademicManagement\DTOs\UpdateAulaDTO;
use App\Modules\AcademicManagement\Models\Aula;
use App\Modules\AcademicManagement\Models\Modulo;
use App\Modules\AcademicManagement\Requests\StoreAulaRequest;
use App\Modules\AcademicManagement\Requests\UpdateAulaRequest;
use App\Modules\AcademicManagement\Resources\AulaResource;
use App\Modules\AcademicManagement\Services\AulaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de aulas dentro de un módulo (PK compuesta).
class AulaController extends Controller
{
    public function __construct(private readonly AulaService $aulas) {}

    #[OA\Get(
        path: '/api/academic-management/modulos/{modulo}/aulas',
        tags: ['Aulas'],
        summary: 'Listar aulas de un módulo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'modulo', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Lista de aulas')]
    )]
    public function index(Modulo $modulo): AnonymousResourceCollection
    {
        return AulaResource::collection($this->aulas->listForModulo($modulo->numero));
    }

    #[OA\Post(
        path: '/api/academic-management/modulos/{modulo}/aulas',
        tags: ['Aulas'],
        summary: 'Crear aula en un módulo',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'modulo', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['numero', 'nombre', 'capacidad', 'piso', 'tipo'],
            properties: [
                new OA\Property(property: 'numero', type: 'integer', example: 1),
                new OA\Property(property: 'nombre', type: 'string', example: 'Laboratorio A'),
                new OA\Property(property: 'capacidad', type: 'integer', example: 40),
                new OA\Property(property: 'piso', type: 'integer', example: 2),
                new OA\Property(property: 'tipo', type: 'string', enum: ['COMUN', 'LABORATORIO', 'AUDITORIO'], example: 'LABORATORIO'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Aula creada', content: new OA\JsonContent(ref: '#/components/schemas/Aula'))]
    )]
    public function store(StoreAulaRequest $request, Modulo $modulo): JsonResponse
    {
        $aula = $this->aulas->create(CreateAulaDTO::fromArray($request->validated(), $modulo->numero));

        return (new AulaResource($aula))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/modulos/{modulo}/aulas/{numero}',
        tags: ['Aulas'],
        summary: 'Ver aula',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'modulo', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'numero', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Aula', content: new OA\JsonContent(ref: '#/components/schemas/Aula')),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function show(Modulo $modulo, int $numero): AulaResource
    {
        return new AulaResource($this->resolve($modulo, $numero));
    }

    #[OA\Put(
        path: '/api/academic-management/modulos/{modulo}/aulas/{numero}',
        tags: ['Aulas'],
        summary: 'Editar aula',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'modulo', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'numero', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'capacidad', type: 'integer'),
                new OA\Property(property: 'piso', type: 'integer'),
                new OA\Property(property: 'tipo', type: 'string', enum: ['COMUN', 'LABORATORIO', 'AUDITORIO']),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Aula actualizada', content: new OA\JsonContent(ref: '#/components/schemas/Aula'))]
    )]
    public function update(UpdateAulaRequest $request, Modulo $modulo, int $numero): AulaResource
    {
        return new AulaResource($this->aulas->update($this->resolve($modulo, $numero), UpdateAulaDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/modulos/{modulo}/aulas/{numero}',
        tags: ['Aulas'],
        summary: 'Eliminar aula',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'modulo', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'numero', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Aula eliminada')]
    )]
    public function destroy(Modulo $modulo, int $numero): JsonResponse
    {
        $this->aulas->delete($this->resolve($modulo, $numero));

        return response()->json(['message' => 'Aula eliminada.']);
    }

    // Resuelve el aula por (modulo, numero) o lanza 404.
    private function resolve(Modulo $modulo, int $numero): Aula
    {
        return $this->aulas->find($modulo->numero, $numero) ?? abort(404, 'Aula no encontrada.');
    }
}
