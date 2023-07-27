<?php
declare(strict_types=1);

return [
    'decoders' => [
        /* --- Examples of decoders ---
        'chain' => [
            'list' => [
                'basic',
                'jwt-header',
                'user-apikey'
            ]
        ],
        'basic' => null,
        'user-apikey' => null,
        'jwt-header' => [
            'driver' => 'auth0',
            'options' => [
                'cache_path' => 'path/to/cache', // Cache the JWKS lookup
                'valid_audiences' => ['id1', 'id2'],
                'authorized_iss' => ['xyz.auth0', 'abc.goog'],
                'private_key' => 'someprivatekeystring',
                'allowed_algos' => ['HS256', 'RS256']
            ]
        ],
        'jwt-param' => [
            'driver' => 'firebase',
            'options' => [
                'algo' => 'HS256',
                'allowed_algos' => ['HS256', 'RS256'],
                'leeway' => 15,
                'param' => 'authParam',
                'private_key' => 'someprivatekeystring',
                'public_key' => 'somepublickeystring',
            ]
        ]
        */
    ],
    'default_decoder' => null,
];
