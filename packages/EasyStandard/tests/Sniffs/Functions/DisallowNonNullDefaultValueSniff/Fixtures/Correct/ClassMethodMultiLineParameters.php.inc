<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Functions\DisallowNonNullDefaultValueSniff\Fixtures\Correct;

use stdClass;

final class TestClass
{
    public function someMethod(
        int $param1,
        array $param2,
        ?array $param3 = null,
        ?int $param4 = null,
        $param5 = null,
        ?stdClass $class = null
    ): void {
        // No body needed
    }
}
