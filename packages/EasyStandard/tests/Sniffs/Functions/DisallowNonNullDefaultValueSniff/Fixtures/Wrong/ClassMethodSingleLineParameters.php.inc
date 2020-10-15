<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Sniffs\Functions\DisallowNonNullDefaultValueSniff\Fixtures\Wrong;

use stdClass;

final class TestClass
{
    /**
     * @var string
     */
    private const CONST = 'const';

    public function someMethod(int $param1, ?stdClass $class, string $const3 = self::CONST, array $param4 = []): void
    {
        // No body needed
    }
}
