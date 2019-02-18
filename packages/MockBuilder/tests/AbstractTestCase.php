<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Tests;

use Closure;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    /**
     * Create mock configured in a closure
     *
     * @param string $class
     * @param \Closure|null $closure
     *
     * @return \Mockery\MockInterface
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Inherited from Mockery
     */
    protected function mock(string $class, ?Closure $closure = null): MockInterface
    {
        $mock = Mockery::mock($class);

        if ($closure !== null) {
            $closure($mock);
        }

        return $mock;
    }

    protected function tearDown()
    {
        if (\class_exists('Mockery')) {
            if (($container = Mockery::getContainer()) !== null) {
                $this->addToAssertionCount($container->mockery_getExpectationCount());
            }

            Mockery::close();
        }

        parent::tearDown();
    }
}