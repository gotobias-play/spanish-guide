<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Paths
    |--------------------------------------------------------------------------
    |
    | You can enable CORS for 1 or multiple paths.
    | Example: `'api/*', 'sanctum/csrf-cookie'`
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Specifies which methods are allowed.
    | Example: `['GET', 'POST', 'PUT', 'DELETE']`
    |
    */

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Specifies which origins are allowed to access the resources.
    | Example: `['http://localhost:3000']`
    |
    */

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | You can use patterns to match origins.
    | Example: `['http://localhost:*']`
    |
    */

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Specifies which headers are allowed.
    | Example: `['Content-Type', 'X-Auth-Token']`
    |
    */

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Specifies which headers are exposed to the browser.
    | Example: `['Cache-Control', 'Content-Language']`
    |
    */

    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Specifies the maximum age of the preflight request in seconds.
    |
    */

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Specifies whether the resource supports credentials.
    |
    */

    'supports_credentials' => true,

];
