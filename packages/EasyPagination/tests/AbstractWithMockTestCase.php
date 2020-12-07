<?php

declare(strict_types=1);

namespace EonX\EasyPagination\Tests;

use Mockery\MockInterface;

abstract class AbstractWithMockTestCase extends AbstractTestCase
{
    /**
     * @param mixed $target
     */
    protected function mock($target, ?callable $expectations = null): MockInterface
    {
        $mock = \Mockery::mock($target);

        if ($expectations !== null) {
            \call_user_func($expectations, $mock);
        }

        return $mock;
    }

    protected function tearDown(): void
    {
//        \dump(\Mockery::getContainer()->getMocks());

//        $this->addToAssertionCount(\Mockery::getContainer()->mockery_getExpectationCount());
//
//        \Mockery::close();

        parent::tearDown();
    }
}
