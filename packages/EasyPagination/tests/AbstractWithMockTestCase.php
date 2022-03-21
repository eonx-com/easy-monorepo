<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Mockery\LegacyMockInterface;

abstract class AbstractWithMockTestCase extends AbstractTestCase
{
    /**
     * @param mixed $target
     */
    protected function mock($target, ?callable $expectations = null): LegacyMockInterface
    {
        $mock = \Mockery::mock($target);

        if ($expectations !== null) {
            \call_user_func($expectations, $mock);
        }

        return $mock;
    }
}
