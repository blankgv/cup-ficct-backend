<?php

namespace App\Modules\Payments\Gateways;

use App\Modules\Payments\Models\Pago;
use Stripe\StripeClient;
use Stripe\Webhook;

// Pasarela Stripe (Checkout hosted). Todo se lee de config/services.stripe.
class StripeGateway implements PaymentGateway
{
    public const NOMBRE = 'stripe';

    private function client(): StripeClient
    {
        return new StripeClient((string) config('services.stripe.secret'));
    }

    public function checkout(Pago $pago): string
    {
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => (string) config('services.stripe.success_url'),
            'cancel_url' => (string) config('services.stripe.cancel_url'),
            'client_reference_id' => (string) $pago->id,
            'metadata' => ['pago_id' => (string) $pago->id],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => (string) config('services.stripe.currency'),
                    'unit_amount' => (int) round((float) $pago->monto * 100),
                    'product_data' => ['name' => $pago->concepto],
                ],
            ]],
        ]);

        $pago->update(['gateway' => self::NOMBRE, 'referencia' => $session->id]);

        return (string) $session->url;
    }

    /**
     * Verifica la firma del webhook y devuelve el id de pago si el pago se completó.
     */
    public function pagoIdDesdeWebhook(string $payload, ?string $signature): ?int
    {
        $event = Webhook::constructEvent($payload, (string) $signature, (string) config('services.stripe.webhook_secret'));

        if ($event->type !== 'checkout.session.completed') {
            return null;
        }

        $pagoId = $event->data->object->metadata->pago_id ?? null;

        return $pagoId !== null ? (int) $pagoId : null;
    }
}
