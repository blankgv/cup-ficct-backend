<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\DTOs\CreatePagoDTO;
use App\Modules\Payments\DTOs\UpdatePagoDTO;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Requests\StorePagoRequest;
use App\Modules\Payments\Requests\UpdatePagoRequest;
use App\Modules\Payments\Resources\PagoResource;
use App\Modules\Payments\Services\PagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// CRUD de pagos.
class PagoController extends Controller
{
    public function __construct(private readonly PagoService $pagos) {}

    #[OA\Get(
        path: '/api/payments/pagos',
        tags: ['Pagos'],
        summary: 'Listar pagos',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'postulante', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'convocatoria', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Lista de pagos')]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return PagoResource::collection(
            $this->pagos->list($request->query('postulante'), $request->query('convocatoria') ? (int) $request->query('convocatoria') : null, (int) $request->query('per_page', 15))
        );
    }

    #[OA\Post(
        path: '/api/payments/pagos',
        tags: ['Pagos'],
        summary: 'Registrar pago (estado PENDIENTE)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['postulante_documento', 'convocatoria_id', 'monto', 'concepto', 'metodo', 'fecha_pago'],
            properties: [
                new OA\Property(property: 'postulante_documento', type: 'string', example: '9876543'),
                new OA\Property(property: 'convocatoria_id', type: 'integer', example: 1),
                new OA\Property(property: 'monto', type: 'number', format: 'float', example: 350.00),
                new OA\Property(property: 'concepto', type: 'string', example: 'Inscripción CUP 2026'),
                new OA\Property(property: 'metodo', type: 'string', enum: ['EFECTIVO', 'TRANSFERENCIA', 'QR', 'TARJETA'], example: 'QR'),
                new OA\Property(property: 'fecha_pago', type: 'string', format: 'date-time', example: '2026-01-15 10:30'),
            ]
        )),
        responses: [new OA\Response(response: 201, description: 'Pago registrado', content: new OA\JsonContent(ref: '#/components/schemas/Pago'))]
    )]
    public function store(StorePagoRequest $request): JsonResponse
    {
        $pago = $this->pagos->create(CreatePagoDTO::fromArray($request->validated()));

        return (new PagoResource($pago))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/payments/pagos/{pago}',
        tags: ['Pagos'],
        summary: 'Ver pago',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pago', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Pago', content: new OA\JsonContent(ref: '#/components/schemas/Pago'))]
    )]
    public function show(Pago $pago): PagoResource
    {
        return new PagoResource($pago);
    }

    #[OA\Put(
        path: '/api/payments/pagos/{pago}',
        tags: ['Pagos'],
        summary: 'Editar pago',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pago', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'monto', type: 'number', format: 'float'),
                new OA\Property(property: 'concepto', type: 'string'),
                new OA\Property(property: 'metodo', type: 'string', enum: ['EFECTIVO', 'TRANSFERENCIA', 'QR', 'TARJETA']),
                new OA\Property(property: 'fecha_pago', type: 'string', format: 'date-time'),
            ]
        )),
        responses: [new OA\Response(response: 200, description: 'Pago actualizado', content: new OA\JsonContent(ref: '#/components/schemas/Pago'))]
    )]
    public function update(UpdatePagoRequest $request, Pago $pago): PagoResource
    {
        return new PagoResource($this->pagos->update($pago, UpdatePagoDTO::fromArray($request->validated())));
    }

    #[OA\Delete(
        path: '/api/payments/pagos/{pago}',
        tags: ['Pagos'],
        summary: 'Eliminar pago',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pago', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Pago eliminado')]
    )]
    public function destroy(Pago $pago): JsonResponse
    {
        $this->pagos->delete($pago);

        return response()->json(['message' => 'Pago eliminado.']);
    }
}
