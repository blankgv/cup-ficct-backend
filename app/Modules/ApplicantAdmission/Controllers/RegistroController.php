<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\Requests\RegistroPostulanteRequest;
use App\Modules\ApplicantAdmission\Services\RegistroPostulanteService;
use App\Modules\Authentication\Resources\AuthTokenResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

// Auto-registro público de postulantes (sin autenticación).
class RegistroController extends Controller
{
    public function __construct(private readonly RegistroPostulanteService $service) {}

    #[OA\Post(
        path: '/api/applicant-admission/registro',
        tags: ['ApplicantAdmission'],
        summary: 'Registro público de postulante (crea cuenta y autentica)',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['documento', 'nombres', 'apellidos', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'documento', type: 'string', example: '1234567'),
                new OA\Property(property: 'nombres', type: 'string', example: 'Juan'),
                new OA\Property(property: 'apellidos', type: 'string', example: 'Perez'),
                new OA\Property(property: 'email', type: 'string', example: 'juan@correo.com'),
                new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '70000000'),
                new OA\Property(property: 'password', type: 'string', example: 'secret123'),
                new OA\Property(property: 'password_confirmation', type: 'string', example: 'secret123'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Registrado', content: new OA\JsonContent(ref: '#/components/schemas/AuthToken')),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function register(RegistroPostulanteRequest $request): JsonResponse
    {
        return (new AuthTokenResource($this->service->register($request->validated())))
            ->response()
            ->setStatusCode(201);
    }
}
