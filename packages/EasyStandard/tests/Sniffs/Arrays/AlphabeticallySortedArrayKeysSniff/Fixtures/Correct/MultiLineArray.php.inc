<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff\Fixtures\Correct;

$array = [
    'a' => 1,
    'b' => 3,
    'z' => 0,
];

$array2 = array(
    'a' => 1,
    'b' => 3,
    'z' => 0,
);

$array3 = [
    'a' => 1,
    'z',
    'b' => 3,
    'h',
    'z' => 0,
];
