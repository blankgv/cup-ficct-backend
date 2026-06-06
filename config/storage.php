<?php

// Carpetas dentro del bucket R2 (configurables por entorno).
return [
    'r2' => [
        'titulos' => env('R2_TITULOS_PATH', 'titulos_bachiller'),
        'fotos' => env('R2_FOTOS_PATH', 'fotos_perfil'),
        'comprobantes' => env('R2_COMPROBANTES_PATH', 'comprobantes'),
        'recibos' => env('R2_RECIBOS_PATH', 'recibos'),
    ],
];
