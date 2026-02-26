<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the CORS settings for your Laravel application.
    | Only scheme + host + port should be listed in 'allowed_origins'.
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'values/*',  // optional: add other Laravel routes outside api/
    ],

    'allowed_methods' => ['*'], // allow all methods

    'allowed_origins' => [
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:9000',
        'http://127.0.0.1:9000',
        'https://192.168.1.7:9000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // allow all headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // change to true if using cookies/auth
];
