<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Doctrine\Stubs;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;

final class ManagerRegistryStub implements ManagerRegistry
{
    /**
     * @param \Doctrine\Persistence\ObjectManager[] $managers
     */
    public function __construct(
        private array $managers,
    ) {
    }

    public function getAliasNamespace(string $alias): string
    {
        return $alias;
    }

    public function getConnection(?string $name = null): never
    {
        throw new RuntimeException('Not implemented.');
    }

    /**
     * @return string[]
     */
    public function getConnectionNames(): array
    {
        return ['default'];
    }

    /**
     * @return object[]
     */
    public function getConnections(): array
    {
        return [];
    }

    public function getDefaultConnectionName(): string
    {
        return 'default';
    }

    public function getDefaultManagerName(): string
    {
        return 'default';
    }

    public function getManager($name = null): ObjectManager
    {
        return $this->managers[$name ?? $this->getDefaultManagerName()];
    }

    public function getManagerForClass(string $class): ?ObjectManager
    {
        return null;
    }

    /**
     * @return string[]
     */
    public function getManagerNames(): array
    {
        $return = [];

        // To reproduce doctrine behavior
        foreach ($this->managers as $name => $manager) {
            $return[$name] = $manager::class;
        }

        return $return;
    }

    /**
     * @return \Doctrine\Persistence\ObjectManager[]
     */
    public function getManagers(): array
    {
        return $this->managers;
    }

    public function getRepository(string $persistentObject, ?string $persistentManagerName = null): never
    {
        throw new RuntimeException('Not implemented.');
    }

    public function resetManager(?string $name = null): ObjectManager
    {
        return $this->managers[$name ?? $this->getDefaultManagerName()];
    }
}
