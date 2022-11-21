<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Doctrine\Stubs;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

final class ManagerRegistryStub implements ManagerRegistry
{
    /**
     * @var \Doctrine\Persistence\ObjectManager[]
     */
    private $managers;

    /**
     * @param \Doctrine\Persistence\ObjectManager[] $managers
     */
    public function __construct(array $managers)
    {
        $this->managers = $managers;
    }

    /**
     * @param string $alias The alias.
     */
    public function getAliasNamespace($alias): string
    {
        return $alias;
    }

    /**
     * @param null|string $name
     *e
     *
     * @return object
     */
    public function getConnection($name = null)
    {
        throw new \RuntimeException('not implemented');
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

    /**
     * @param string $class A persistent object class name.
     */
    public function getManagerForClass($class): ?ObjectManager
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
            $return[$name] = \get_class($manager);
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

    /**
     * @param string $persistentObject
     * @param string $persistentManagerName
     */
    public function getRepository($persistentObject, $persistentManagerName = null): ObjectRepository
    {
        throw new \RuntimeException('not implemented');
    }

    /**
     * @param string|null $name
     */
    public function resetManager($name = null): ObjectManager
    {
        return $this->managers[$name ?? $this->getDefaultManagerName()];
    }
}
