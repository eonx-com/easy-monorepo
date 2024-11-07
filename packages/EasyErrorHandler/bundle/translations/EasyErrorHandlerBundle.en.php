<?php
declare(strict_types=1);

return [
    'exceptions' => [
        // @todo Remove `entity_not_valid` in 7.0
        'entity_not_valid' => 'Entity validation failed.',
        'bad_request' => 'Bad request.',
        'conflict' => 'Conflict.',
        'default_user_message' => 'Oops, something went wrong.',
        'forbidden' => 'Forbidden.',
        'not_found' => 'Not found.',
        'not_valid' => 'Validation failed.',
        'unauthorized' => 'Unauthorized.',
    ],
    'violations' => [
        // @todo Remove `another_iri` in 7.0
        'another_iri' => 'This value should be %iri% IRI.',
        // @todo Remove `invalid_type` in 7.0
        'invalid_type' => 'The type of the value should be "%expected_types%", "%current_type%" given.',
        // @todo Remove `missing_constructor_argument` in 7.0
        'missing_constructor_argument' => 'This value should be present.',
        // @todo Rename `invalid_type_new` to `invalid_type` in 7.0
        'invalid_type_new' => 'This value should be of type %expected_type%.',
        'invalid_datetime' => 'This value is not a valid date/time.',
        'invalid_iri' => 'This value should be an IRI.',
        'not_encodable' => 'The input data is misformatted.',
    ],
];
