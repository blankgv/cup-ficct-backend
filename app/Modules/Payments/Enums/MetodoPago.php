<?php

namespace App\Modules\Payments\Enums;

// Método de pago (lista fija).
enum MetodoPago: string
{
    case EFECTIVO = 'EFECTIVO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
    case QR = 'QR';
    case TARJETA = 'TARJETA';
}
