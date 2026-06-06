<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Models\Comprobante;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Requests\UploadComprobanteRequest;
use App\Modules\Payments\Resources\ComprobanteResource;
use App\Modules\Payments\Services\ComprobanteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

// Comprobantes de pago (subida y descarga firmada).
class ComprobanteController extends Controller
{
    public function __construct(private readonly ComprobanteService $comprobantes) {}

    #[OA\Get(
        path: '/api/payments/pagos/{pago}/comprobantes',
        tags: ['Pagos'],
        summary: 'Listar comprobantes de un pago',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pago', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Lista de comprobantes')]
    )]
    public function index(Pago $pago): AnonymousResourceCollection
    {
        return ComprobanteResource::collection($pago->comprobantes()->latest()->get());
    }

    #[OA\Post(
        path: '/api/payments/pagos/{pago}/comprobantes',
        tags: ['Pagos'],
        summary: 'Subir comprobante del pago (PDF/imagen, máx 5 MB)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pago', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['comprobante'],
                properties: [new OA\Property(property: 'comprobante', type: 'string', format: 'binary')]
            )
        )),
        responses: [new OA\Response(response: 201, description: 'Comprobante subido')]
    )]
    public function store(UploadComprobanteRequest $request, Pago $pago): JsonResponse
    {
        $comprobante = $this->comprobantes->upload($pago, $request->file('comprobante'));

        return (new ComprobanteResource($comprobante))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/payments/comprobantes/{comprobante}/descargar',
        tags: ['Pagos'],
        summary: 'Descargar comprobante (redirige a URL firmada)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'comprobante', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 302, description: 'Redirige al archivo')]
    )]
    public function download(Comprobante $comprobante): RedirectResponse
    {
        return redirect()->away($this->comprobantes->downloadUrl($comprobante));
    }
}
