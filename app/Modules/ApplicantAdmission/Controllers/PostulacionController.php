<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\DTOs\CreatePostulacionDTO;
use App\Modules\ApplicantAdmission\Models\Postulacion;
use App\Modules\ApplicantAdmission\Models\Postulante;
use App\Modules\ApplicantAdmission\Requests\StorePostulacionRequest;
use App\Modules\ApplicantAdmission\Resources\PostulacionResource;
use App\Modules\ApplicantAdmission\Services\PostulacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// Postulaciones de un postulante.
class PostulacionController extends Controller
{
    public function __construct(private readonly PostulacionService $postulaciones) {}

    #[OA\Get(
        path: '/api/applicant-admission/postulantes/{postulante}/postulaciones',
        tags: ['Postulaciones'],
        summary: 'Listar postulaciones del postulante',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Postulaciones')]
    )]
    public function index(Postulante $postulante): AnonymousResourceCollection
    {
        return PostulacionResource::collection($this->postulaciones->listForPostulante($postulante->documento));
    }

    #[OA\Post(
        path: '/api/applicant-admission/postulantes/{postulante}/postulaciones',
        tags: ['Postulaciones'],
        summary: 'Registrar postulación (1ra y 2da opción)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['convocatoria_id', 'carrera_primera_codigo', 'carrera_segunda_codigo'],
            properties: [
                new OA\Property(property: 'convocatoria_id', type: 'integer', example: 1),
                new OA\Property(property: 'carrera_primera_codigo', type: 'string', example: '187-09'),
                new OA\Property(property: 'carrera_segunda_codigo', type: 'string', example: '187-10'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Postulación creada (PENDIENTE)', content: new OA\JsonContent(ref: '#/components/schemas/Postulacion')),
            new OA\Response(response: 422, description: 'Convocatoria cerrada, duplicada o carrera no ofertada'),
        ]
    )]
    public function store(StorePostulacionRequest $request, Postulante $postulante): JsonResponse
    {
        $postulacion = $this->postulaciones->create(
            CreatePostulacionDTO::fromArray($request->validated(), $postulante->documento)
        );

        return (new PostulacionResource($postulacion))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/applicant-admission/postulantes/{postulante}/postulaciones/{convocatoria}',
        tags: ['Postulaciones'],
        summary: 'Ver postulación',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Postulación', content: new OA\JsonContent(ref: '#/components/schemas/Postulacion'))]
    )]
    public function show(Postulante $postulante, int $convocatoria): PostulacionResource
    {
        return new PostulacionResource($this->resolve($postulante, $convocatoria));
    }

    #[OA\Delete(
        path: '/api/applicant-admission/postulantes/{postulante}/postulaciones/{convocatoria}',
        tags: ['Postulaciones'],
        summary: 'Cancelar postulación',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'postulante', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Postulación cancelada')]
    )]
    public function destroy(Postulante $postulante, int $convocatoria): JsonResponse
    {
        $this->postulaciones->delete($this->resolve($postulante, $convocatoria));

        return response()->json(['message' => 'Postulación cancelada.']);
    }

    private function resolve(Postulante $postulante, int $convocatoria): Postulacion
    {
        return $this->postulaciones->find($postulante->documento, $convocatoria) ?? abort(404, 'Postulación no encontrada.');
    }
}
