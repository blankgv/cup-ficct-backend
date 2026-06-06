<?php

namespace App\Modules\Payments\Gateways;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

// Resuelve la pasarela de pago por nombre (o la del entorno por defecto).
class PaymentGatewayFactory
{
    /** @var array<string, class-string<PaymentGateway>> */
    private const GATEWAYS = [
        StripeGateway::NOMBRE => StripeGateway::class,
        PayPalGateway::NOMBRE => PayPalGateway::class,
    ];

    public function __construct(private readonly Container $app) {}

    public function make(?string $nombre = null): PaymentGateway
    {
        $nombre = $nombre ?: (string) config('payments.default_gateway');

        $clase = self::GATEWAYS[$nombre] ?? null;

        if ($clase === null) {
            throw new InvalidArgumentException("Pasarela de pago no soportada: {$nombre}");
        }

        return $this->app->make($clase);
    }
}
