<?php

use PHPOpenSourceSaver\JWTAuth\Providers\Auth\Illuminate as AuthProvider;
use PHPOpenSourceSaver\JWTAuth\Providers\JWT\Lcobucci;
use PHPOpenSourceSaver\JWTAuth\Providers\Storage\Illuminate as StorageProvider;

// Config JWT. Genera el secreto con: php artisan jwt:secret

return [

    // Secreto para firmar tokens.
    'secret' => env('JWT_SECRET'),

    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    // Valores desde .env.
    'ttl' => env('JWT_TTL'),

    'refresh_ttl' => env('JWT_REFRESH_TTL'),

    'algo' => env('JWT_ALGO'),

    'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],

    'persistent_claims' => [],

    'lock_subject' => true,

    'leeway' => env('JWT_LEEWAY'),

    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED'),

    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD'),

    'show_black_list_exception' => env('JWT_SHOW_BLACKLIST_EXCEPTION'),

    'decrypt_cookies' => false,

    'providers' => [
        'jwt' => Lcobucci::class,
        'auth' => AuthProvider::class,
        'storage' => StorageProvider::class,
    ],

];
