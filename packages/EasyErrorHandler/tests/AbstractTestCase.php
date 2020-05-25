<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
class AbstractTestCase extends TestCase
{
    /**
     * @param string|object $className
     *
     * @throws \ReflectionException
     */
    protected function getPropertyAsPublic($className, string $propertyName): ReflectionProperty
    {
        $class = new ReflectionClass($className);
        $method = $class->getProperty($propertyName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param string|object $class
     */
    protected function mock($class, ?callable $expectations = null): LegacyMockInterface
    {
        $mock = Mockery::mock($class);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
