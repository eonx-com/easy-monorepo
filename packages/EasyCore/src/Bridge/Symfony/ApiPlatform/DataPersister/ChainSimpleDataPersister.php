<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ChainSimpleDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface[]
     */
    private $cache = [];

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface
     */
    private $decorated;

    /**
     * @var mixed[]
     */
    private $persisters;

    /**
     * @param mixed[] $persisters
     */
    public function __construct(ContainerInterface $container, ContextAwareDataPersisterInterface $decorated, array $persisters)
    {
        $this->container = $container;
        $this->decorated = $decorated;
        $this->persisters = $persisters;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function persist($data, ?array $context = null)
    {
        return $this->getDataPersister($data)
            ? $this->getDataPersister($data)->persist($data, $context ?? [])
            : $this->decorated->persist($data, $context ?? []);
    }

    /**
     * @param mixed $data
     */
    public function remove($data, ?array $context = null): void
    {
        $this->getDataPersister($data)
            ? $this->getDataPersister($data)->remove($data, $context ?? [])
            : $this->decorated->remove($data, $context ?? []);
    }

    /**
     * @param mixed $data
     */
    public function supports($data, ?array $context = null): bool
    {
        return $this->getDataPersister($data)
            ? $this->getDataPersister($data)->supports($data, $context ?? [])
            : $this->decorated->supports($data, $context ?? []);
    }

    /**
     * @param mixed $data
     */
    private function getDataPersister($data): ?ContextAwareDataPersisterInterface
    {
        if (\is_object($data) === false) {
            return null;
        }

        $class = \get_class($data);

        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        if (isset($this->persisters[$class]) === false) {
            return null;
        }

        return $this->cache[$class] = $this->container->get($this->persisters[$class]);
    }
}
