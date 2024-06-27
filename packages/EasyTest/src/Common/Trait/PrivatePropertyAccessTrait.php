<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Trait;

use LogicException;
use ReflectionProperty;

trait PrivatePropertyAccessTrait
{
    public function callPrivateMethod(object $object, string $method, mixed ...$args): mixed
    {
        return (fn () => $this->{$method}(...$args))
            ->call($object);
    }

    protected static function getPrivatePropertyValue(object $object, string $propertyName): mixed
    {
        return self::resolvePropertyReflection($object, $propertyName)->getValue($object);
    }

    protected static function setPrivatePropertyValue(object $object, string $propertyName, mixed $value): void
    {
        self::resolvePropertyReflection($object, $propertyName)->setValue($object, $value);
    }

    private static function resolvePropertyReflection(object $object, string $propertyName): ReflectionProperty
    {
        $objectClass = $object::class;

        while (\property_exists($objectClass, $propertyName) === false) {
            $objectClass = \get_parent_class($objectClass);

            if ($objectClass === false) {
                throw new LogicException(\sprintf('The $%s property does not exist.', $propertyName));
            }
        }

        return new ReflectionProperty($objectClass, $propertyName);
    }
}
