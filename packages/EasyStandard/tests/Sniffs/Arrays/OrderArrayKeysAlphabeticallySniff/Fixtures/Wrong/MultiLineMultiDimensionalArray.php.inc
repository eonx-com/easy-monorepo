<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Arrays\OrderArrayKeysAlphabeticallySniff\Fixtures\Wrong;

$array = [
    'z' => 0,
    'a' => 1,
    'f' => [
        'b' => 1,
        'a' => 2,
    ],
    'b' => 3,
];

$array2 = array(
    'z' => 0,
    'a' => 1,
    'f' => [
        'b' => 1,
        'a' => 2,
    ],
    'b' => 3,
);
