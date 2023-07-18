<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests;

use LogicException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTestCase extends TestCase
{
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    protected function getPrivatePropertyValue(object $object, string $propertyName): mixed
    {
        return $this->resolvePropertyReflection($object, $propertyName)
            ->getValue($object);
    }

    protected function mock(mixed $target, ?callable $expectations = null): MockInterface
    {
        /** @var \Mockery\MockInterface $mock */
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
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
