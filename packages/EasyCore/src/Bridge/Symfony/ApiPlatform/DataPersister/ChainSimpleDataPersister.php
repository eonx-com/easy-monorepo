<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Event\DataPersisterResolvedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ChainSimpleDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var \ApiPlatform\Core\DataPersister\DataPersisterInterface
     */
    private $cached;

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
        EventDispatcherInterface $dispatcher,
        array $simplePersisters,
        iterable $persisters
    ) {
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

    /**
     * @param mixed $data
     */
    private function getSimpleDataPersister($data): ?ContextAwareDataPersisterInterface
    {
        if (\is_object($data) === false) {
            return null;
        }

        return $this->simplePersisters[$data::class] ?? null;
    }
}
