<?php

namespace App\Modules\Payments\Enums;

// Estado del pago.
enum EstadoPago: string
{
    case PENDIENTE = 'PENDIENTE';
    case PAGADO = 'PAGADO';
    case RECHAZADO = 'RECHAZADO';
}
