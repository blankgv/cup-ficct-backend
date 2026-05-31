<?php

namespace App\Modules\AcademicManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\DTOs\CreateHorarioDTO;
use App\Modules\AcademicManagement\DTOs\UpdateHorarioDTO;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\GrupoMateria;
use App\Modules\AcademicManagement\Models\Horario;
use App\Modules\AcademicManagement\Requests\StoreHorarioRequest;
use App\Modules\AcademicManagement\Requests\UpdateHorarioRequest;
use App\Modules\AcademicManagement\Resources\HorarioResource;
use App\Modules\AcademicManagement\Services\HorarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// Horarios de un grupo en una materia (PK compuesta).
class HorarioController extends Controller
{
    public function __construct(private readonly HorarioService $horarios) {}

    #[OA\Get(
        path: '/api/academic-management/grupos/{grupo}/materias/{sigla}/horarios',
        tags: ['Horarios'],
        summary: 'Listar horarios del grupo en una materia',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sigla', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Horarios')]
    )]
    public function index(Grupo $grupo, string $sigla): AnonymousResourceCollection
    {
        $this->ensureAssociation($grupo, $sigla);

        return HorarioResource::collection($this->horarios->list($grupo->id, $sigla));
    }

    #[OA\Post(
        path: '/api/academic-management/grupos/{grupo}/materias/{sigla}/horarios',
        tags: ['Horarios'],
        summary: 'Crear horario',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sigla', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['dia', 'hora_inicio', 'hora_fin', 'aula_modulo_numero', 'aula_numero'],
            properties: [
                new OA\Property(property: 'dia', type: 'string', enum: ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'], example: 'LUNES'),
                new OA\Property(property: 'hora_inicio', type: 'string', example: '07:00'),
                new OA\Property(property: 'hora_fin', type: 'string', example: '09:00'),
                new OA\Property(property: 'aula_modulo_numero', type: 'string', example: '236'),
                new OA\Property(property: 'aula_numero', type: 'integer', example: 14),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Horario creado', content: new OA\JsonContent(ref: '#/components/schemas/Horario')),
            new OA\Response(response: 422, description: 'Solapamiento o datos inválidos'),
        ]
    )]
    public function store(StoreHorarioRequest $request, Grupo $grupo, string $sigla): JsonResponse
    {
        $this->ensureAssociation($grupo, $sigla);

        $horario = $this->horarios->create(CreateHorarioDTO::fromArray($request->validated(), $grupo->id, $sigla));

        return (new HorarioResource($horario))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/academic-management/grupos/{grupo}/materias/{sigla}/horarios/{numero}',
        tags: ['Horarios'],
        summary: 'Ver horario',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sigla', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'numero', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Horario', content: new OA\JsonContent(ref: '#/components/schemas/Horario'))]
    )]
    public function show(Grupo $grupo, string $sigla, int $numero): HorarioResource
    {
        return new HorarioResource($this->resolve($grupo, $sigla, $numero));
    }

    #[OA\Put(
        path: '/api/academic-management/grupos/{grupo}/materias/{sigla}/horarios/{numero}',
        tags: ['Horarios'],
        summary: 'Editar horario',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sigla', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'numero', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'dia', type: 'string', enum: ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO']),
                new OA\Property(property: 'hora_inicio', type: 'string', example: '07:00'),
                new OA\Property(property: 'hora_fin', type: 'string', example: '09:00'),
                new OA\Property(property: 'aula_modulo_numero', type: 'string'),
                new OA\Property(property: 'aula_numero', type: 'integer'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Horario actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Horario'))]
    )]
    public function update(UpdateHorarioRequest $request, Grupo $grupo, string $sigla, int $numero): HorarioResource
    {
        return new HorarioResource($this->horarios->update($this->resolve($grupo, $sigla, $numero), UpdateHorarioDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/academic-management/grupos/{grupo}/materias/{sigla}/horarios/{numero}',
        tags: ['Horarios'],
        summary: 'Eliminar horario',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sigla', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'numero', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Horario eliminado')]
    )]
    public function destroy(Grupo $grupo, string $sigla, int $numero): JsonResponse
    {
        $this->horarios->delete($this->resolve($grupo, $sigla, $numero));

        return response()->json(['message' => 'Horario eliminado.']);
    }

    // El grupo debe cursar la materia.
    private function ensureAssociation(Grupo $grupo, string $sigla): void
    {
        $exists = GrupoMateria::query()
            ->where('grupo_id', $grupo->id)
            ->where('materia_sigla', $sigla)
            ->exists();

        if (! $exists) {
            abort(404, 'El grupo no cursa esa materia.');
        }
    }

    private function resolve(Grupo $grupo, string $sigla, int $numero): Horario
    {
        $this->ensureAssociation($grupo, $sigla);

        return $this->horarios->find($grupo->id, $sigla, $numero) ?? abort(404, 'Horario no encontrado.');
    }
}
