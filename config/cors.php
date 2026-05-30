<?php

// Config CORS para el frontend Next.js.

// Orígenes permitidos, leídos de .env.
$allowedOrigins = array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', (string) env('FRONTEND_URL')))
));

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With', 'Origin'],

    'exposed_headers' => [],

    'max_age' => 0,

    // API con Bearer token: sin cookies.
    'supports_credentials' => false,

];
