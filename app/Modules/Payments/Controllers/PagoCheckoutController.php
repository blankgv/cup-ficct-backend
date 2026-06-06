<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Gateways\StripeGateway;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Services\PagoCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

// Cobro por pasarela (Stripe) y webhook de confirmación.
class PagoCheckoutController extends Controller
{
    public function __construct(private readonly PagoCheckoutService $checkout) {}

    #[OA\Post(
        path: '/api/payments/pagos/{pago}/checkout',
        tags: ['Pagos'],
        summary: 'Iniciar cobro por pasarela (devuelve URL de pago)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pago', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'URL de la pasarela', content: new OA\JsonContent(
            properties: [new OA\Property(property: 'url', type: 'string', example: 'https://checkout.stripe.com/...')]
        ))]
    )]
    public function checkout(Pago $pago): JsonResponse
    {
        return response()->json(['url' => $this->checkout->checkout($pago)]);
    }

    #[OA\Post(
        path: '/api/payments/webhook/stripe',
        tags: ['Pagos'],
        summary: 'Webhook de Stripe (confirma el pago)',
        responses: [new OA\Response(response: 200, description: 'Procesado')]
    )]
    public function webhookStripe(Request $request, StripeGateway $stripe): JsonResponse
    {
        try {
            $pagoId = $stripe->pagoIdDesdeWebhook($request->getContent(), $request->header('Stripe-Signature'));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Firma inválida.'], 400);
        }

        if ($pagoId !== null && ($pago = Pago::find($pagoId)) !== null) {
            $this->checkout->marcarPagado($pago);
        }

        return response()->json(['received' => true]);
    }
}
