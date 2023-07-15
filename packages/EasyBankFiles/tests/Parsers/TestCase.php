<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Tests\Parsers;

use EonX\EasyBankFiles\Tests\TestCases\TestCase as BaseTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @covers \EonX\EasyBankFiles\Tests\Parsers\TestCase
 */
class TestCase extends BaseTestCase
{
    /**
     * Get mock for given class and set expectations based on given callable.
     */
    protected function getMockWithExpectations(string $class, callable $setExpectations): LegacyMockInterface
    {
        $mock = Mockery::mock($class);

        $setExpectations($mock);

        return $mock;
    }

    /**
     * Set the protected/private function to accessible and return reflection method.
     *
     * @throws \ReflectionException
     *
     * @phpstan-param class-string $class
     */
    protected function getProtectedMethod(string $class, string $method): ReflectionMethod
    {
        $reflectionClass = new ReflectionClass($class);

        $function = $reflectionClass->getMethod($method);
        $function->setAccessible(true);

        return $function;
    }

    /**
     * Set property to accessible and return reflection property.
     *
     * @throws \ReflectionException
     *
     * @phpstan-param class-string $class
     */
    protected function getProtectedProperty(string $class, string $property): ReflectionProperty
    {
        $reflectionClass = new ReflectionClass($class);

        $prop = $reflectionClass->getProperty($property);
        $prop->setAccessible(true);

        return $prop;
    }
}
