<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreateMateriaDTO;
use App\Modules\AcademicManagement\DTOs\UpdateMateriaDTO;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\AcademicManagement\Requests\StoreMateriaRequest;
use App\Modules\AcademicManagement\Requests\UpdateMateriaRequest;
use App\Modules\AcademicManagement\Resources\MateriaResource;
use App\Modules\AcademicManagement\Services\MateriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de materias.
class MateriaController extends Controller
{
    public function __construct(private readonly MateriaService $materias) {}

    #[OA\Get(
        path: '/api/academic-management/materias',
        tags: ['Materias'],
        summary: 'Listar materias',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de materias'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return MateriaResource::collection(
            $this->materias->list($request->query('search'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/academic-management/materias',
        tags: ['Materias'],
        summary: 'Crear materia',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['nombre', 'sigla', 'peso'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string', example: 'Matemática'),
                new OA\Property(property: 'sigla', type: 'string', example: 'MAT'),
                new OA\Property(property: 'peso', type: 'number', format: 'float', example: 0.25),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Materia creada', content: new OA\JsonContent(ref: '#/components/schemas/Materia'))]
    )]
    public function store(StoreMateriaRequest $request): JsonResponse
    {
        $materia = $this->materias->create(CreateMateriaDTO::fromArray($request->validated()));

        return (new MateriaResource($materia))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/materias/{materia}',
        tags: ['Materias'],
        summary: 'Ver materia',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'materia', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Materia', content: new OA\JsonContent(ref: '#/components/schemas/Materia'))]
    )]
    public function show(Materia $materia): MateriaResource
    {
        return new MateriaResource($materia);
    }

    #[OA\Put(
        path: '/api/academic-management/materias/{materia}',
        tags: ['Materias'],
        summary: 'Editar materia',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'materia', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'sigla', type: 'string'),
                new OA\Property(property: 'peso', type: 'number', format: 'float'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Materia actualizada', content: new OA\JsonContent(ref: '#/components/schemas/Materia'))]
    )]
    public function update(UpdateMateriaRequest $request, Materia $materia): MateriaResource
    {
        return new MateriaResource($this->materias->update($materia, UpdateMateriaDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/materias/{materia}',
        tags: ['Materias'],
        summary: 'Eliminar materia',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'materia', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Materia eliminada')]
    )]
    public function destroy(Materia $materia): JsonResponse
    {
        $this->materias->delete($materia);

        return response()->json(['message' => 'Materia eliminada.']);
    }
}
