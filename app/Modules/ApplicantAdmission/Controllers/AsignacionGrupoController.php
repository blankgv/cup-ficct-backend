<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Services\AsignacionGrupoService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

// Generación y asignación automática de grupos de una convocatoria.
class AsignacionGrupoController extends Controller
{
    public function __construct(private readonly AsignacionGrupoService $asignacion) {}

    #[OA\Post(
        path: '/api/applicant-admission/convocatorias/{convocatoria}/generar-grupos',
        tags: ['Convocatorias'],
        summary: 'Generar grupos y asignar automáticamente a los elegibles (VERIFICADO + PAGADO). Regenera si ya existían',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Resumen', content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'grupos_creados', type: 'integer', example: 4),
                new OA\Property(property: 'inscritos', type: 'integer', example: 250),
                new OA\Property(property: 'manana', type: 'integer', example: 130),
                new OA\Property(property: 'tarde', type: 'integer', example: 120),
            ]
        ))]
    )]
    public function generar(Convocatoria $convocatoria): JsonResponse
    {
        return response()->json($this->asignacion->generar($convocatoria));
    }
}
