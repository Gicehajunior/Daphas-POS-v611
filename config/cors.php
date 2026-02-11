<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', '/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        '*',
        'http://localhost',
        'http://localhost:8500',
        'http://127.0.0.1:8500',
        'http://localhost:8600',
        'http://127.0.0.1:8600',
        'http://localhost:9000',
        'http://localhost/esd_api_bridger.php',
        'http://localhost:9000/api/values/PostTims',
        'http://127.0.0.1:9000',
        'http://127.0.0.1:9000',
        'http://127.0.0.1/esd_api_bridger.php',
        'http://127.0.0.1:9000/api/values/PostTims',
        'https://192.168.1.7:9000/api/values/PostTims',
        'http://127.0.0.1:8200/daraja_api/confirm_payment_request',
        'http://127.0.0.1:8200/daraja_api/validate_payment_request', 
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, 
];
