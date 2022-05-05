<?php

declare(strict_types=1);

return [
    'exceptions' => [
        'default_user_message' => 'Oops, something went wrong.',
        'bad_request' => 'Bad request.',
        'conflict' => 'Conflict.',
        'entity_not_valid' => 'Entity validation failed.',
        'error_codes_provider' => [
            'not_configured' => 'Please create a class that implements the ErrorCodesProviderInterface',
        ],
        'forbidden' => 'Forbidden.',
        'not_found' => 'Not found.',
        'not_valid' => 'Validation failed.',
        'unauthorized' => 'Unauthorized.',
    ],
];
