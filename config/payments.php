<?php

// Configuración del módulo de pagos.
return [
    // Pasarela por defecto cuando el checkout no especifica una (stripe|paypal).
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY'),

    // Cobro de inscripción generado automáticamente al verificar una postulación.
    'inscripcion' => [
        'monto' => (float) env('PAYMENT_INSCRIPCION_MONTO', 250),
        'concepto' => env('PAYMENT_INSCRIPCION_CONCEPTO', 'Inscripción CUP'),
        'metodo' => env('PAYMENT_INSCRIPCION_METODO', 'TARJETA'),
    ],
];
