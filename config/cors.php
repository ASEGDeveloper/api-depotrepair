<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'], // your frontend
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // required if sending cookies/tokens
];




// return [

//     'paths' => ['api/*', 'sanctum/csrf-cookie'],
//     'allowed_methods' => ['*'],
//     'allowed_origins' => [
//         'http://localhost:3000', // Next.js frontend
//     ],
//     'allowed_origins_patterns' => [],
//     'allowed_headers' => ['*'],
//     'exposed_headers' => [],
//     'max_age' => 0,
//     'supports_credentials' => true,
// ];
