<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Mockery;
use Mockery\LegacyMockInterface;

abstract class AbstractWithMockTestCase extends AbstractTestCase
{
    protected function mock(mixed $target, ?callable $expectations = null): LegacyMockInterface
    {
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
