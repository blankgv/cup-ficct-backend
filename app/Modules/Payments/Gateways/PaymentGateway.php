<?php

namespace App\Modules\Payments\Gateways;

use App\Modules\Payments\Models\Pago;

// Contrato de pasarela de pago (Stripe, PayPal, etc.).
interface PaymentGateway
{
    /**
     * Crea una sesión de pago para el pago dado y devuelve la URL de redirección.
     */
    public function checkout(Pago $pago): string;
}
