<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests;

use LogicException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    public function getPrivatePropertyValue(object $object, string $propertyName): mixed
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(true);

        return $propertyReflection->getValue($object);
    }

    public function setPrivatePropertyValue(object $object, string $propertyName, mixed $value): void
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($object, $value);
    }

    /**
     * @param class-string $target
     */
    protected function mock(string $target, ?callable $expectations = null): MockInterface
    {
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    private function resolvePropertyReflection(object $object, string $propertyName): ReflectionProperty
    {
        while (\property_exists($object, $propertyName) === false) {
            $object = \get_parent_class($object);

            if ($object === false) {
                throw new LogicException(\sprintf('The $%s property does not exist.', $propertyName));
            }
        }

        return new ReflectionProperty($object, $propertyName);
    }
}
