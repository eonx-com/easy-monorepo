<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Dispatchers;

use EonX\EasyCore\Doctrine\Events\EntityCreatedEvent;
use EonX\EasyCore\Doctrine\Events\EntityUpdatedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

final class DeferredEntityEventDispatcher implements DeferredEntityEventDispatcherInterface
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var array<int, object[]>
     */
    private $entityInsertions = [];

    /**
     * @var array<int, object[]>
     */
    private $entityUpdates = [];

    /**
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->enabled = true;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function clear(?int $transactionNestingLevel = null): void
    {
        if ($transactionNestingLevel !== null) {
            foreach (\array_keys($this->entityInsertions) as $level) {
                if ($level >= $transactionNestingLevel) {
                    $this->entityInsertions[$level] = [];
                }
            }

            foreach (\array_keys($this->entityUpdates) as $level) {
                if ($level >= $transactionNestingLevel) {
                    $this->entityUpdates[$level] = [];
                }
            }

            return;
        }

        $this->entityInsertions = [];
        $this->entityUpdates = [];
    }

    public function deferInsertions(array $entityInsertions, int $transactionNestingLevel): void
    {
        if ($this->enabled === false) {
            return;
        }

        /** @var object[] $mergedEntityInsertions */
        $mergedEntityInsertions = \array_merge(
            $this->entityInsertions[$transactionNestingLevel] ?? [],
            $entityInsertions
        );
        $this->entityInsertions[$transactionNestingLevel] = $mergedEntityInsertions;
    }

    public function deferUpdates(array $entityUpdates, int $transactionNestingLevel): void
    {
        if ($this->enabled === false) {
            return;
        }

        /** @var object[] $mergedEntityUpdates */
        $mergedEntityUpdates = \array_merge(
            $this->entityUpdates[$transactionNestingLevel] ?? [],
            $entityUpdates
        );
        $this->entityUpdates[$transactionNestingLevel] = $mergedEntityUpdates;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function dispatch(): void
    {
        $entityInsertions = $this->entityInsertions;
        $entityUpdates = $this->entityUpdates;

        $this->clear();

        if ($this->enabled === false) {
            return;
        }

        $processedEntities = [];
        foreach ($entityInsertions as $entities) {
            foreach ($entities as $oid => $entity) {
                $processedEntities[$oid] = $entity;
                $this->eventDispatcher->dispatch(new EntityCreatedEvent($entity));
            }
        }

        foreach ($entityUpdates as $entities) {
            foreach ($entities as $oid => $entity) {
                if (\array_key_exists($oid, $processedEntities)) {
                    continue;
                }
                $processedEntities[$oid] = $entity;
                $this->eventDispatcher->dispatch(new EntityUpdatedEvent($entity));
            }
        }
    }

    public function enable(): void
    {
        $this->enabled = true;
    }
}
