<?php
declare(strict_types=1);

return [
    // @todo Make translations consistent with \Symfony\Component\HttpFoundation\Response::$statusTexts and \GuzzleHttp\Psr7\Response::PHRASES in 7.0
    'exceptions' => [
        'bad_request' => 'Bad request.',
        'conflict' => 'Conflict.',
        'default_user_message' => 'Oops, something went wrong.',
        // @todo Remove `entity_not_valid` in 7.0
        'entity_not_valid' => 'Entity validation failed.',
        'forbidden' => 'Forbidden.',
        'not_found' => 'Not found.',
        'not_valid' => 'Validation failed.',
        'unauthorized' => 'Unauthorized.',
    ],
    'violations' => [
        // @todo Remove `another_iri` in 7.0
        'another_iri' => 'This value should be %iri% IRI.',
        'invalid_datetime' => 'This value is not a valid date/time.',
        'invalid_enum' => 'The value should be a valid choice.',
        'invalid_iri' => 'This value should be an IRI.',
        'invalid_type' => 'This value should be of type %expected_types%.',
        // @todo Remove `missing_constructor_argument` in 7.0
        'missing_constructor_argument' => 'This value should be present.',
        'not_encodable' => 'The input data is misformatted.',
    ],
];
