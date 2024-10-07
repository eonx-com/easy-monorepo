<?php
declare(strict_types=1);

return [
    'exceptions' => [
        'bad_request' => 'Bad request.',
        'conflict' => 'Conflict.',
        'default_user_message' => 'Oops, something went wrong.',
        'entity_not_valid' => 'Entity validation failed.',
        'forbidden' => 'Forbidden.',
        'not_found' => 'Not found.',
        'not_valid' => 'Validation failed.',
        'unauthorized' => 'Unauthorized.',
    ],
    'violations' => [
        'another_iri' => 'This value should be %iri% IRI.',
        'invalid_datetime' => 'This value is not a valid date/time.',
        'invalid_iri' => 'This value should be an IRI.',
        'invalid_type' => 'The type of the value should be "%expected_types%", "%current_type%" given.',
        'missing_constructor_argument' => 'This value should be present.',
        'not_encodable' => 'The input data is misformatted.',
    ],
];
