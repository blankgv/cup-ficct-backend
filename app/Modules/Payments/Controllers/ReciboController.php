<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Services\ReciboService;
use Illuminate\Http\RedirectResponse;
use OpenApi\Attributes as OA;

// Recibo PDF del pago.
class ReciboController extends Controller
{
    public function __construct(private readonly ReciboService $recibos) {}

    #[OA\Get(
        path: '/api/payments/pagos/{pago}/recibo',
        tags: ['Pagos'],
        summary: 'Descargar recibo del pago (PDF, solo PAGADO; redirige a URL firmada)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pago', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 302, description: 'Redirige al PDF'),
            new OA\Response(response: 422, description: 'El pago no está confirmado'),
        ]
    )]
    public function download(Pago $pago): RedirectResponse
    {
        return redirect()->away($this->recibos->urlDescarga($pago));
    }
}
