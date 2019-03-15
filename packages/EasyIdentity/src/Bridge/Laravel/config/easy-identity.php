<?php
declare(strict_types=1);

return [
    'implementations' => [
        'auth0' => [
            'client_id' => \env('AUTH0_CLIENT_ID'),
            'client_secret' => \env('AUTH0_CLIENT_SECRET'),
            'connection' => \env('AUTH0_CONNECTION', 'DEV'),
            'domain' => \env('AUTH0_DOMAIN', 'your-domain.auth0.com')
        ]
    ]
];
