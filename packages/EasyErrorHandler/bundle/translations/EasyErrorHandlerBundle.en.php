<?php
declare(strict_types=1);

return [
    'exceptions' => [
        'bad_request' => 'Bad Request',
        'conflict' => 'Conflict',
        'default_user_message' => 'Oops, something went wrong.',
        'forbidden' => 'Forbidden',
        'not_found' => 'Not Found',
        'not_valid' => 'Validation failed.',
        'unauthorized' => 'Unauthorized',
    ],
    'violations' => [
        'invalid_datetime' => 'This value is not a valid date/time.',
        'invalid_enum' => 'The value should be a valid choice.',
        'invalid_iri' => 'This value should be an IRI.',
        'invalid_type' => 'This value should be of type %expected_types%.',
        'not_encodable' => 'The input data is misformatted.',
    ],
];
