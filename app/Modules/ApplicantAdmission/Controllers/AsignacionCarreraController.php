<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Services\AsignacionCarreraService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

// Asignación automática de carrera definitiva por nota y cupos.
class AsignacionCarreraController extends Controller
{
    public function __construct(private readonly AsignacionCarreraService $asignacion) {}

    #[OA\Post(
        path: '/api/applicant-admission/convocatorias/{convocatoria}/asignar-carreras',
        tags: ['Convocatorias'],
        summary: 'Asignar carrera definitiva a los aprobados (promedio ≥ 60 + habilitados), por nota desc y cupos. Regenera',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Resumen', content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'elegibles', type: 'integer', example: 180),
                new OA\Property(property: 'asignados', type: 'integer', example: 150),
                new OA\Property(property: 'sin_cupo', type: 'integer', example: 30),
                new OA\Property(property: 'por_carrera', type: 'object', example: ['187-09' => 80, '187-10' => 70]),
            ]
        ))]
    )]
    public function generar(Convocatoria $convocatoria): JsonResponse
    {
        return response()->json($this->asignacion->generar($convocatoria));
    }
}
