<?php

namespace App\Modules\Evaluation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicManagement\Models\Grupo;
use App\Modules\AcademicManagement\Models\Materia;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\Evaluation\Requests\BatchAsistenciasRequest;
use App\Modules\Evaluation\Requests\StoreAsistenciaRequest;
use App\Modules\Evaluation\Resources\AsistenciaResource;
use App\Modules\Evaluation\Services\AsistenciaService;
use App\Modules\Evaluation\Services\EvaluacionAccessGuard;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

// Registro y consulta de asistencia.
class AsistenciaController extends Controller
{
    public function __construct(
        private readonly AsistenciaService $asistencias,
        private readonly EvaluacionAccessGuard $guard,
    ) {}

    #[OA\Post(
        path: '/api/evaluation/asistencias',
        tags: ['Asistencia'],
        summary: 'Registrar/actualizar la asistencia de un postulante en una fecha',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['postulante_documento', 'convocatoria_id', 'materia_sigla', 'fecha', 'estado'],
            properties: [
                new OA\Property(property: 'postulante_documento', type: 'string', example: '9876543'),
                new OA\Property(property: 'convocatoria_id', type: 'integer', example: 1),
                new OA\Property(property: 'materia_sigla', type: 'string', example: 'MAT'),
                new OA\Property(property: 'fecha', type: 'string', format: 'date', example: '2026-03-01'),
                new OA\Property(property: 'estado', type: 'string', enum: ['PRESENTE', 'AUSENTE', 'JUSTIFICADO'], example: 'PRESENTE'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Asistencia guardada'),
            new OA\Response(response: 422, description: 'No inscrito o materia ajena al grupo'),
        ]
    )]
    public function store(StoreAsistenciaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->guard->assertMateriaDeInscripcion(
            (string) $data['postulante_documento'],
            (int) $data['convocatoria_id'],
            (string) $data['materia_sigla'],
        );

        // Upsert idempotente → 200 siempre.
        return (new AsistenciaResource($this->asistencias->upsert($data)))->response()->setStatusCode(200);
    }

    #[OA\Post(
        path: '/api/evaluation/grupos/{grupo}/materias/{materia}/asistencias',
        tags: ['Asistencia'],
        summary: 'Carga masiva de asistencia de una fecha para una materia del grupo',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'grupo', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'materia', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['fecha', 'asistencias'],
            properties: [
                new OA\Property(property: 'fecha', type: 'string', format: 'date', example: '2026-03-01'),
                new OA\Property(property: 'asistencias', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'postulante_documento', type: 'string', example: '9876543'),
                        new OA\Property(property: 'estado', type: 'string', enum: ['PRESENTE', 'AUSENTE', 'JUSTIFICADO'], example: 'PRESENTE'),
                    ]
                )),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Resumen {guardadas, omitidas, no_inscritos}')]
    )]
    public function storeBatch(BatchAsistenciasRequest $request, Grupo $grupo, Materia $materia): JsonResponse
    {
        $this->guard->assertGrupoMateria($grupo->id, $materia->sigla);
        $data = $request->validated();

        return response()->json($this->asistencias->batch($grupo, $materia, $data['fecha'], $data['asistencias']));
    }

    #[OA\Get(
        path: '/api/evaluation/postulantes/{postulante}/convocatorias/{convocatoria}/asistencia',
        tags: ['Asistencia'],
        summary: 'Reporte: % de asistencia por materia, % global y habilitación (≥ 80%)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Reporte de asistencia'),
            new OA\Response(response: 422, description: 'No inscrito'),
        ]
    )]
    public function reporte(Postulante $postulante, int $convocatoria): JsonResponse
    {
        $this->guard->assertEnGrupoDeInscripcion($postulante->documento, $convocatoria);

        return response()->json($this->asistencias->reporte($postulante, $convocatoria));
    }
}
