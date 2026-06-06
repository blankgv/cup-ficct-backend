<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Gateways\PayPalGateway;
use App\Modules\Payments\Gateways\StripeGateway;
use App\Modules\Payments\Models\Pago;
use App\Modules\Payments\Services\PagoCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

// Cobro por pasarela (Stripe / PayPal) y webhooks de confirmación.
class PagoCheckoutController extends Controller
{
    public function __construct(private readonly PagoCheckoutService $checkout) {}

    #[OA\Post(
        path: '/api/payments/pagos/{pago}/checkout',
        tags: ['Pagos'],
        summary: 'Iniciar cobro por pasarela (devuelve URL de pago)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'pago', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'gateway', in: 'query', description: 'Pasarela (stripe|paypal). Si se omite, usa la del entorno.', schema: new OA\Schema(type: 'string', enum: ['stripe', 'paypal'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'URL de la pasarela', content: new OA\JsonContent(
                properties: [new OA\Property(property: 'url', type: 'string', example: 'https://www.paypal.com/checkoutnow?token=...')]
            )),
            new OA\Response(response: 422, description: 'Pasarela no soportada'),
        ]
    )]
    public function checkout(Request $request, Pago $pago): JsonResponse
    {
        try {
            $url = $this->checkout->checkout($pago, $request->query('gateway'));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['url' => $url]);
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

    #[OA\Post(
        path: '/api/payments/webhook/paypal',
        tags: ['Pagos'],
        summary: 'Webhook de PayPal (confirma el pago)',
        responses: [new OA\Response(response: 200, description: 'Procesado')]
    )]
    public function webhookPaypal(Request $request, PayPalGateway $paypal): JsonResponse
    {
        $headers = [
            'paypal-auth-algo' => $request->header('paypal-auth-algo'),
            'paypal-cert-url' => $request->header('paypal-cert-url'),
            'paypal-transmission-id' => $request->header('paypal-transmission-id'),
            'paypal-transmission-sig' => $request->header('paypal-transmission-sig'),
            'paypal-transmission-time' => $request->header('paypal-transmission-time'),
        ];

        try {
            $pagoId = $paypal->pagoIdDesdeWebhook($request->getContent(), $headers);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Firma inválida.'], 400);
        }

        if ($pagoId !== null && ($pago = Pago::find($pagoId)) !== null) {
            $this->checkout->marcarPagado($pago);
        }

        return response()->json(['received' => true]);
    }
}
