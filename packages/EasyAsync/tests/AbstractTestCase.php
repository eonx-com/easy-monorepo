<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
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
        $this->addToAssertionCount(\Mockery::getContainer()->mockery_getExpectationCount());

        \Mockery::close();

        parent::tearDown();
    }
}
