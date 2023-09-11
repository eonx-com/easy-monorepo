<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests;

use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    protected function getMethodAsPublic(string $className, string $methodName): ReflectionMethod
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    protected function mock(mixed $target, ?callable $expectations = null): LegacyMockInterface
    {
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }
}
