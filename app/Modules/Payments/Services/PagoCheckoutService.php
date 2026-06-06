<?php

namespace App\Modules\Payments\Services;

use App\Modules\Payments\Enums\EstadoPago;
use App\Modules\Payments\Gateways\PaymentGatewayFactory;
use App\Modules\Payments\Models\Pago;

// Inicia el cobro por pasarela y confirma el pago.
class PagoCheckoutService
{
    public function __construct(private readonly PaymentGatewayFactory $gateways) {}

    // Crea la sesión de pago en la pasarela elegida y devuelve la URL de redirección.
    public function checkout(Pago $pago, ?string $gateway = null): string
    {
        return $this->gateways->make($gateway)->checkout($pago);
    }

    // Marca el pago como PAGADO (lo llama el webhook al confirmarse).
    public function marcarPagado(Pago $pago): Pago
    {
        $pago->update(['estado' => EstadoPago::PAGADO->value]);

        return $pago;
    }
}
