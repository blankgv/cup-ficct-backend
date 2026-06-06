<?php

// Configuración del módulo de pagos.
return [
    // Pasarela por defecto cuando el checkout no especifica una (stripe|paypal).
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY'),
];
