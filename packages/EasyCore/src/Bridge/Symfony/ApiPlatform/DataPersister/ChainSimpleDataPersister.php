<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ChainSimpleDataPersister implements DataPersisterInterface
{
    /**
     * @var \ApiPlatform\Core\DataPersister\DataPersisterInterface[]
     */
    private $cache = [];

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \ApiPlatform\Core\DataPersister\DataPersisterInterface
     */
    private $decorated;

    /**
     * @var mixed[]
     */
    private $persisters;

    /**
     * @param mixed[] $persisters
     */
    public function __construct(ContainerInterface $container, DataPersisterInterface $decorated, array $persisters)
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
    public function persist($data)
    {
        return $this->getDataPersister($data)
            ? $this->getDataPersister($data)->persist($data)
            : $this->decorated->persist($data);
    }

    /**
     * @param mixed $data
     */
    public function remove($data): void
    {
        $this->getDataPersister($data)
            ? $this->getDataPersister($data)->remove($data)
            : $this->decorated->remove($data);
    }

    /**
     * @param mixed $data
     */
    public function supports($data): bool
    {
        return $this->getDataPersister($data)
            ? $this->getDataPersister($data)->supports($data)
            : $this->decorated->supports($data);
    }

    /**
     * @param mixed $data
     */
    private function getDataPersister($data): ?DataPersisterInterface
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
