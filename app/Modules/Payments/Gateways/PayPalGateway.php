<?php

namespace App\Modules\Payments\Gateways;

use App\Modules\Payments\Models\Pago;
use Illuminate\Support\Facades\Http;
use RuntimeException;

// Pasarela PayPal (Orders v2, REST por Http nativo). Todo se lee de config/services.paypal.
class PayPalGateway implements PaymentGateway
{
    public const NOMBRE = 'paypal';

    // URL base de la API (sandbox o producción), sin barra final.
    private function baseUrl(): string
    {
        return rtrim((string) config('services.paypal.base_url'), '/');
    }

    // Token OAuth de aplicación (client_credentials).
    private function token(): string
    {
        $resp = Http::asForm()
            ->withBasicAuth((string) config('services.paypal.client_id'), (string) config('services.paypal.secret'))
            ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw();

        return (string) $resp->json('access_token');
    }

    public function checkout(Pago $pago): string
    {
        $resp = Http::withToken($this->token())
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'custom_id' => (string) $pago->id,
                    'description' => $pago->concepto,
                    'amount' => [
                        'currency_code' => (string) config('services.paypal.currency'),
                        'value' => number_format((float) $pago->monto, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => (string) config('services.paypal.success_url'),
                    'cancel_url' => (string) config('services.paypal.cancel_url'),
                ],
            ])->throw();

        $order = $resp->json();
        $pago->update(['gateway' => self::NOMBRE, 'referencia' => $order['id'] ?? null]);

        // Link de aprobación al que se redirige al usuario.
        foreach ($order['links'] ?? [] as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                return (string) $link['href'];
            }
        }

        throw new RuntimeException('PayPal no devolvió enlace de aprobación.');
    }

    /**
     * Verifica la firma del webhook y devuelve el id de pago si la captura se completó.
     *
     * @param array<string, ?string> $headers
     */
    public function pagoIdDesdeWebhook(string $payload, array $headers): ?int
    {
        $evento = json_decode($payload, true);

        $verificacion = Http::withToken($this->token())
            ->post($this->baseUrl().'/v1/notifications/verify-webhook-signature', [
                'auth_algo' => $headers['paypal-auth-algo'] ?? null,
                'cert_url' => $headers['paypal-cert-url'] ?? null,
                'transmission_id' => $headers['paypal-transmission-id'] ?? null,
                'transmission_sig' => $headers['paypal-transmission-sig'] ?? null,
                'transmission_time' => $headers['paypal-transmission-time'] ?? null,
                'webhook_id' => (string) config('services.paypal.webhook_id'),
                'webhook_event' => $evento,
            ])->throw();

        if ($verificacion->json('verification_status') !== 'SUCCESS') {
            throw new RuntimeException('Firma de webhook PayPal inválida.');
        }

        if (($evento['event_type'] ?? null) !== 'PAYMENT.CAPTURE.COMPLETED') {
            return null;
        }

        $customId = $evento['resource']['custom_id'] ?? null;

        return $customId !== null ? (int) $customId : null;
    }
}
