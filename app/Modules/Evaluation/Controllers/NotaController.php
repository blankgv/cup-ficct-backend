<?php

namespace App\Modules\Evaluation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Evaluation\Requests\BatchNotasRequest;
use App\Modules\Evaluation\Requests\StoreNotaRequest;
use App\Modules\Evaluation\Resources\NotaResource;
use App\Modules\Evaluation\Services\EvaluacionAccessGuard;
use App\Modules\Evaluation\Services\NotaService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

// Carga y consulta de notas.
class NotaController extends Controller
{
    public function __construct(
        private readonly NotaService $notas,
        private readonly EvaluacionAccessGuard $guard,
    ) {}

    #[OA\Get(
        path: '/api/evaluation/mis-grupos',
        tags: ['Notas'],
        summary: 'Grupos y materias del usuario (docente: los asignados; staff: todos)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista de grupo-materias')]
    )]
    public function misGrupos(): JsonResponse
    {
        return response()->json($this->guard->misGrupos());
    }

    #[OA\Post(
        path: '/api/evaluation/notas',
        tags: ['Notas'],
        summary: 'Cargar/actualizar una nota (examen) de un postulante',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['postulante_documento', 'convocatoria_id', 'materia_sigla', 'numero', 'valor'],
            properties: [
                new OA\Property(property: 'postulante_documento', type: 'string', example: '9876543'),
                new OA\Property(property: 'convocatoria_id', type: 'integer', example: 1),
                new OA\Property(property: 'materia_sigla', type: 'string', example: 'MAT'),
                new OA\Property(property: 'numero', type: 'integer', example: 1),
                new OA\Property(property: 'valor', type: 'number', format: 'float', example: 75.5),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Nota guardada'),
            new OA\Response(response: 422, description: 'No inscrito o materia ajena al grupo'),
        ]
    )]
    public function store(StoreNotaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->guard->assertMateriaDeInscripcion(
            (string) $data['postulante_documento'],
            (int) $data['convocatoria_id'],
            (string) $data['materia_sigla'],
        );

        // Upsert idempotente → 200 siempre (no 201 aunque cree el registro).
        return (new NotaResource($this->notas->upsert($data)))->response()->setStatusCode(200);
    }

    #[OA\Post(
        path: '/api/evaluation/grupos/{grupo}/materias/{materia}/notas',
        tags: ['Notas'],
        summary: 'Carga masiva de un examen para una materia del grupo',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'materia', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['numero', 'notas'],
            properties: [
                new OA\Property(property: 'numero', type: 'integer', example: 1),
                new OA\Property(property: 'notas', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'postulante_documento', type: 'string', example: '9876543'),
                        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 80),
                    ]
                )),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Resumen {guardadas, omitidas, no_inscritos}')]
    )]
    public function storeBatch(BatchNotasRequest $request, Grupo $grupo, Materia $materia): JsonResponse
    {
        $this->guard->assertGrupoMateria($grupo->id, $materia->sigla);
        $data = $request->validated();

        return response()->json($this->notas->batch($grupo, $materia, (int) $data['numero'], $data['notas']));
    }

    #[OA\Get(
        path: '/api/evaluation/postulantes/{postulante}/convocatorias/{convocatoria}/boletin',
        tags: ['Notas'],
        summary: 'Boletín: notas, promedio ponderado y estado (APROBADO/REPROBADO)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Boletín'),
            new OA\Response(response: 422, description: 'No inscrito'),
        ]
    )]
    public function boletin(Postulante $postulante, int $convocatoria): JsonResponse
    {
        $this->guard->assertEnGrupoDeInscripcion($postulante->documento, $convocatoria);

        return response()->json($this->notas->boletin($postulante, $convocatoria));
    }
}
