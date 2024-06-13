<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Fixture\App\ObjectManager;

use Doctrine\Persistence\ObjectManager;

final class NotSupportedObjectManager implements ObjectManager
{
    public function __call(string $name, array $arguments)
    {
    }

    public function clear(): void
    {

    }

    public function contains(object $object): void
    {
    }

    public function detach(object $object): void
    {
    }

    public function find(string $className, $id): void
    {
    }

    public function flush(): void
    {
        // TODO: Implement flush() method.
    }

    public function getClassMetadata(string $className): void
    {
    }

    public function getMetadataFactory(): void
    {
    }

    public function getRepository(string $className)
    {
        // TODO: Implement getRepository() method.
    }

    public function initializeObject(object $obj): void
    {
    }

    public function isUninitializedObject(mixed $value): bool
    {
        return true;
    }

    public function persist(object $object): void
    {
    }

    public function refresh(object $object): void
    {
    }

    public function remove(object $object): void
    {
    }
}