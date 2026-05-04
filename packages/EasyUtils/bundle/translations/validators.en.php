<?php
declare(strict_types=1);

return [
    'abn' => [
        'not_valid' => 'This field must be an 11-digit string representing a valid Australian Business Number.',
    ],
    'alphanumeric' => [
        'not_valid' => 'This value may only contain letters and numbers.',
    ],
    'alphanumeric_hyphen' => [
        'not_valid' => 'This value may only contain letters, numbers, and hyphens.',
    ],
    'date_interval' => [
        'not_valid' => 'This value is not a valid DateInterval.',
    ],
    'decimal' => [
        'not_valid' => 'This value is not a valid decimal or integer number, has less than {{ minPrecision }} ' .
            'or more than {{ maxPrecision }} digits in precision.',
    ],
    'integer' => [
        'not_valid' => 'This value should be of type integer.',
    ],
    'number' => [
        'should_be_greater_or_equal' => 'This value should be greater than or equal to {{ compared_value }}.',
        'should_be_less_or_equal' => 'This value should be less than or equal to {{ compared_value }}.',
        'should_be_positive_or_zero' => 'This value should be positive or zero.',
    ],
];
