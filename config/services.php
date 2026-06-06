<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Pasarela de pago Stripe.
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'usd'),
        // URLs del frontend a las que Stripe redirige tras el pago.
        'success_url' => env('STRIPE_SUCCESS_URL', env('FRONTEND_URL').'/pagos/exito'),
        'cancel_url' => env('STRIPE_CANCEL_URL', env('FRONTEND_URL').'/pagos/cancelado'),
    ],

    // API de feriados (pre-llena la tabla; la facultad luego edita).
    'feriados' => [
        'api_url' => env('FERIADOS_API_URL'),
        'pais' => env('FERIADOS_PAIS'),
    ],

    // OpenAI: interpreta el texto del reporte por voz (todo del entorno).
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL'),
        'base_url' => env('OPENAI_BASE_URL'),
    ],

    // Pasarela de pago PayPal (todo se lee del entorno).
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        // URL base de la API: sandbox o producción (se define en el entorno).
        'base_url' => env('PAYPAL_BASE_URL'),
        'currency' => env('PAYPAL_CURRENCY'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        // URLs del frontend a las que PayPal redirige tras la aprobación.
        'success_url' => env('PAYPAL_SUCCESS_URL'),
        'cancel_url' => env('PAYPAL_CANCEL_URL'),
    ],

];
