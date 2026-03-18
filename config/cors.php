<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    | Include your API routes and Sanctum CSRF endpoint (and auth routes if used).
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods / Origins / Headers
    |--------------------------------------------------------------------------
    */
    'allowed_methods' => ['*'],

    // Put your exact frontend origin(s) here:
    // e.g. https://your-domain.com or http://localhost:5173 for Vite dev
    'allowed_origins' => ['*'],
        'https://your-frontend-domain.com',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:80',
        'http://127.0.0.1:80',


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers / Max Age / Credentials
    |--------------------------------------------------------------------------
    */
    'exposed_headers' => [],
    'max_age' => 0,

    // Required for cookies (Sanctum stateful):
    'supports_credentials' => true,
];
