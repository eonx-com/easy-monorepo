<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Event\DataPersisterResolvedEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ChainSimpleDataPersister implements ContextAwareDataPersisterInterface
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
     * @var \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface
     */
    private $decorated;

    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var mixed[]
     */
    private $persisters;

    /**
     * @param mixed[] $persisters
     */
    public function __construct(
        ContainerInterface $container,
        ContextAwareDataPersisterInterface $decorated,
        EventDispatcherInterface $dispatcher,
        array $persisters
    ) {
        $this->container = $container;
        $this->decorated = $decorated;
        $this->dispatcher = $dispatcher;
        $this->persisters = $persisters;
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     *
     * @return mixed
     */
    public function persist($data, ?array $context = null)
    {
        $persister = $this->getDataPersister($data);

        if ($persister) {
            return $persister instanceof ContextAwareDataPersisterInterface
                ? $persister->persist($data, $context ?? [])
                : $persister->persist($data);
        }

        return $this->decorated->persist($data, $context ?? []);
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function remove($data, ?array $context = null): void
    {
        $persister = $this->getDataPersister($data);

        if ($persister) {
            $persister instanceof ContextAwareDataPersisterInterface
                ? $persister->remove($data, $context ?? [])
                : $persister->remove($data);

            return;
        }

        $this->decorated->remove($data, $context ?? []);
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function supports($data, ?array $context = null): bool
    {
        $persister = $this->getDataPersister($data);

        if ($persister) {
            return $persister instanceof ContextAwareDataPersisterInterface
                ? $persister->supports($data, $context ?? [])
                : $persister->supports($data);
        }

        return $this->decorated->supports($data, $context ?? []);
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

        /** @var \ApiPlatform\Core\DataPersister\DataPersisterInterface $dataPersister */
        $dataPersister = $this->container->get($this->persisters[$class]);

        $this->dispatcher->dispatch(new DataPersisterResolvedEvent($dataPersister));

        return $this->cache[$class] = $dataPersister;
    }
}
