<?php

namespace App\Modules\ApplicantAdmission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApplicantAdmission\DTOs\CreateConvocatoriaDTO;
use App\Modules\ApplicantAdmission\DTOs\UpdateConvocatoriaDTO;
use App\Modules\ApplicantAdmission\Models\Convocatoria;
use App\Modules\ApplicantAdmission\Requests\SetCuposRequest;
use App\Modules\ApplicantAdmission\Requests\StoreConvocatoriaRequest;
use App\Modules\ApplicantAdmission\Requests\UpdateConvocatoriaRequest;
use App\Modules\ApplicantAdmission\Resources\CarreraCupoResource;
use App\Modules\ApplicantAdmission\Resources\ConvocatoriaResource;
use App\Modules\ApplicantAdmission\Services\ConvocatoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de convocatorias + cupos por carrera.
class ConvocatoriaController extends Controller
{
    public function __construct(private readonly ConvocatoriaService $convocatorias) {}

    #[OA\Get(
        path: '/api/applicant-admission/convocatorias',
        tags: ['Convocatorias'],
        summary: 'Listar convocatorias',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de convocatorias')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return ConvocatoriaResource::collection(
            $this->convocatorias->list($request->query('search'), (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/applicant-admission/convocatorias',
        tags: ['Convocatorias'],
        summary: 'Crear convocatoria',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['nombre', 'gestion', 'fecha_inicio', 'fecha_fin'],
            properties: [
                new OA\Property(property: 'nombre', type: 'string', example: 'Admisión CUP 2026-I'),
                new OA\Property(property: 'gestion', type: 'string', example: '2026'),
                new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date', example: '2026-01-10'),
                new OA\Property(property: 'fecha_fin', type: 'string', format: 'date', example: '2026-02-10'),
                new OA\Property(property: 'estado', type: 'string', enum: ['ABIERTA', 'CERRADA'], example: 'ABIERTA'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Convocatoria creada', content: new OA\JsonContent(ref: '#/components/schemas/Convocatoria'))]
    )]
    public function store(StoreConvocatoriaRequest $request): JsonResponse
    {
        $convocatoria = $this->convocatorias->create(CreateConvocatoriaDTO::fromArray($request->validated()));

        return (new ConvocatoriaResource($convocatoria))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/applicant-admission/convocatorias/{convocatoria}',
        tags: ['Convocatorias'],
        summary: 'Ver convocatoria',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Convocatoria', content: new OA\JsonContent(ref: '#/components/schemas/Convocatoria'))]
    )]
    public function show(Convocatoria $convocatoria): ConvocatoriaResource
    {
        return new ConvocatoriaResource($convocatoria);
    }

    #[OA\Put(
        path: '/api/applicant-admission/convocatorias/{convocatoria}',
        tags: ['Convocatorias'],
        summary: 'Editar convocatoria',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'nombre', type: 'string'),
                new OA\Property(property: 'gestion', type: 'string'),
                new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date'),
                new OA\Property(property: 'fecha_fin', type: 'string', format: 'date'),
                new OA\Property(property: 'estado', type: 'string', enum: ['ABIERTA', 'CERRADA']),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Convocatoria actualizada', content: new OA\JsonContent(ref: '#/components/schemas/Convocatoria'))]
    )]
    public function update(UpdateConvocatoriaRequest $request, Convocatoria $convocatoria): ConvocatoriaResource
    {
        return new ConvocatoriaResource($this->convocatorias->update($convocatoria, UpdateConvocatoriaDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/applicant-admission/convocatorias/{convocatoria}',
        tags: ['Convocatorias'],
        summary: 'Eliminar convocatoria',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Convocatoria eliminada')]
    )]
    public function destroy(Convocatoria $convocatoria): JsonResponse
    {
        $this->convocatorias->delete($convocatoria);

        return response()->json(['message' => 'Convocatoria eliminada.']);
    }

    #[OA\Get(
        path: '/api/applicant-admission/convocatorias/{convocatoria}/cupos',
        tags: ['Convocatorias'],
        summary: 'Listar cupos por carrera',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Cupos por carrera')]
    )]
    public function cupos(Convocatoria $convocatoria): AnonymousResourceCollection
    {
        return CarreraCupoResource::collection($this->convocatorias->listCupos($convocatoria));
    }

    #[OA\Put(
        path: '/api/applicant-admission/convocatorias/{convocatoria}/cupos',
        tags: ['Convocatorias'],
        summary: 'Fijar cupos de una carrera',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['carrera_codigo', 'cupos'],
            properties: [
                new OA\Property(property: 'carrera_codigo', type: 'string', example: '187-09'),
                new OA\Property(property: 'cupos', type: 'integer', example: 80),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Cupos actualizados')]
    )]
    public function setCupos(SetCuposRequest $request, Convocatoria $convocatoria): AnonymousResourceCollection
    {
        return CarreraCupoResource::collection(
            $this->convocatorias->setCupos($convocatoria, $request->validated('carrera_codigo'), (int) $request->validated('cupos'))
        );
    }

    #[OA\Delete(
        path: '/api/applicant-admission/convocatorias/{convocatoria}/cupos/{carrera}',
        tags: ['Convocatorias'],
        summary: 'Quitar una carrera de la convocatoria',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'convocatoria', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'carrera', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Carrera quitada')]
    )]
    public function removeCarrera(Convocatoria $convocatoria, string $carrera): JsonResponse
    {
        $this->convocatorias->removeCarrera($convocatoria, $carrera);

        return response()->json(['message' => 'Carrera quitada de la convocatoria.']);
    }
}
