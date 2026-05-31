<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\ApplicantAdmission\Requests\RechazarPostulacionRequest;
use App\Modules\ApplicantAdmission\Resources\PostulacionResource;
use App\Modules\ApplicantAdmission\Services\VerificacionService;
use OpenApi\Attributes as OA;

// Verificación de requisitos de la postulación.
class VerificacionController extends Controller
{
    public function __construct(private readonly VerificacionService $verificacion) {}

    #[OA\Put(
        path: '/api/applicant-admission/postulantes/{postulante}/postulaciones/{convocatoria}/verificar',
        tags: ['Postulaciones'],
        summary: 'Verificar requisitos (aprueba la inscripción al CUP)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Postulación VERIFICADO', content: new OA\JsonContent(ref: '#/components/schemas/Postulacion'))]
    )]
    public function verificar(Postulante $postulante, int $convocatoria): PostulacionResource
    {
        return new PostulacionResource($this->verificacion->verificar($this->resolve($postulante, $convocatoria)));
    }

    #[OA\Put(
        path: '/api/applicant-admission/postulantes/{postulante}/postulaciones/{convocatoria}/rechazar',
        tags: ['Postulaciones'],
        summary: 'Rechazar por no cumplir requisitos',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['motivo'],
            properties: [new OA\Property(property: 'motivo', type: 'string', example: 'Falta certificado de nacimiento')]
        )),
        responses: [new OA\Response(response: 200, description: 'Postulación RECHAZADO', content: new OA\JsonContent(ref: '#/components/schemas/Postulacion'))]
    )]
    public function rechazar(RechazarPostulacionRequest $request, Postulante $postulante, int $convocatoria): PostulacionResource
    {
        return new PostulacionResource(
            $this->verificacion->rechazar($this->resolve($postulante, $convocatoria), $request->validated('motivo'))
        );
    }

    private function resolve(Postulante $postulante, int $convocatoria): Postulacion
    {
        return Postulacion::query()
            ->where('postulante_documento', $postulante->documento)
            ->where('convocatoria_id', $convocatoria)
            ->first() ?? abort(404, 'Postulación no encontrada.');
    }
}
