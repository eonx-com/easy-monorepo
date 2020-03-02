<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    /**
     * Create mock for given target and expectations.
     *
     * @param mixed $target
     * @param null|callable $expectations
     *
     * @return \Mockery\MockInterface
     */
    protected function mock($target, ?callable $expectations = null): MockInterface
    {
        $mock = \Mockery::mock($target);

        if ($expectations !== null) {
            \call_user_func($expectations, $mock);
        }

        return $mock;
    }

    /**
     * Close mockery after tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->addToAssertionCount(\Mockery::getContainer()->mockery_getExpectationCount());

        \Mockery::close();

        parent::tearDown();
    }
}
