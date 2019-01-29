<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Tests;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * Create mock for given class and apply expectations if given.
     *
     * @param string $class
     * @param null|callable $expectations
     *
     * @return \Mockery\MockInterface
     */
    protected function mock(string $class, ?callable $expectations = null): MockInterface
    {
        $mock = Mockery::mock($class);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}