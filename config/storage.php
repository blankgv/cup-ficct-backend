<?php

// Carpetas dentro del bucket R2 (configurables por entorno).
return [
    'r2' => [
        'titulos' => env('R2_TITULOS_PATH', 'titulos_bachiller'),
        'fotos' => env('R2_FOTOS_PATH', 'fotos_perfil'),
    ],
];
