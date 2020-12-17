<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Arrays\AlphabeticallySortedArrayKeysSniff\Fixtures\Correct;

$array = ['a' => 1, 'b' => 3, 'f' => ['a' => 2, 'b' => 1], 'z' => 0];

$array2 = array('a' => 1, 'b' => 3, 'f' => array('a' => 2, 'b' => 1), 'z' => 0);
