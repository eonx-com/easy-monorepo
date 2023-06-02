<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Event\DataPersisterResolvedEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;

final class ChainSimpleDataPersister implements ContextAwareDataPersisterInterface, ResetInterface
{
    /**
     * @var null|\ApiPlatform\Core\DataPersister\DataPersisterInterface
     */
    private $cached;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var iterable<\ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface>
     */
    private $persisters;

    /**
     * @var mixed[]
     */
    private $simplePersisters;

    /**
     * @param mixed[] $simplePersisters
     * @param iterable<\ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface> $persisters
     */
    public function __construct(
        ContainerInterface $container,
        EventDispatcherInterface $dispatcher,
        array $simplePersisters,
        iterable $persisters,
    ) {
        $this->container = $container;
        $this->dispatcher = $dispatcher;
        $this->simplePersisters = $simplePersisters;
        $this->persisters = $persisters;
    }

    /**
     * @return \ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface[]
     */
    public function getDataPersisters(): array
    {
        return $this->persisters instanceof \Traversable ? \iterator_to_array($this->persisters) : $this->persisters;
    }

    /**
     * @return mixed[]
     */
    public function getSimpleDataPersisters(): array
    {
        return $this->simplePersisters;
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

        if ($persister === null) {
            return $data;
        }

        return $persister instanceof ContextAwareDataPersisterInterface
            ? $persister->persist($data, $context ?? [])
            : $persister->persist($data);
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function remove($data, ?array $context = null): void
    {
        $persister = $this->getDataPersister($data);

        if ($persister === null) {
            return;
        }

        $persister instanceof ContextAwareDataPersisterInterface
            ? $persister->remove($data, $context ?? [])
            : $persister->remove($data);
    }

    public function reset(): void
    {
        $this->cached = null;
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function supports($data, ?array $context = null): bool
    {
        $persister = $this->getDataPersister($data);

        if ($persister === null) {
            return false;
        }

        return $persister instanceof ContextAwareDataPersisterInterface
            ? $persister->supports($data, $context ?? [])
            : $persister->supports($data);
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    private function getCoreDataPersister($data, ?array $context = null): ?DataPersisterInterface
    {
        foreach ($this->persisters as $persister) {
            if ($persister->supports($data, $context ?? [])) {
                return $persister;
            }
        }

        return null;
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    private function getDataPersister($data, ?array $context = null): ?DataPersisterInterface
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $dataPersister = $this->getSimpleDataPersister($data) ?? $this->getCoreDataPersister($data, $context);

        if ($dataPersister === null) {
            return null;
        }

        $this->dispatcher->dispatch(new DataPersisterResolvedEvent($dataPersister));

        return $this->cached = $dataPersister;
    }

    private function getSimpleDataPersister(mixed $data): ?ContextAwareDataPersisterInterface
    {
        if (\is_object($data) === false || isset($this->simplePersisters[$data::class]) === false) {
            return null;
        }

        return $this->container->get($this->simplePersisters[$data::class]);
    }
}
