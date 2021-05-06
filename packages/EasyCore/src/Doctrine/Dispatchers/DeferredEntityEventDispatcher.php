<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Dispatchers;

use EonX\EasyCore\Doctrine\Events\EntityCreatedEvent;
use EonX\EasyCore\Doctrine\Events\EntityUpdatedEvent;
use EonX\EasyCore\Interfaces\DatabaseEntityInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class DeferredEntityEventDispatcher implements DeferredEntityEventDispatcherInterface
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var array<int, \EonX\EasyCore\Interfaces\DatabaseEntityInterface[]>
     */
    private $entityInsertions = [];

    /**
     * @var array<int, \EonX\EasyCore\Interfaces\DatabaseEntityInterface[]>
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

        /** @var \EonX\EasyCore\Interfaces\DatabaseEntityInterface[] $mergedEntityInsertions */
        $mergedEntityInsertions = \array_merge(
            (array)($this->entityInsertions[$transactionNestingLevel] ?? []),
            $entityInsertions
        );
        $this->entityInsertions[$transactionNestingLevel] = $mergedEntityInsertions;
    }

    public function deferUpdates(array $entityUpdates, int $transactionNestingLevel): void
    {
        if ($this->enabled === false) {
            return;
        }

        /** @var \EonX\EasyCore\Interfaces\DatabaseEntityInterface[] $mergedEntityUpdates */
        $mergedEntityUpdates = \array_merge(
            (array)($this->entityUpdates[$transactionNestingLevel] ?? []),
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

        \array_walk_recursive($entityInsertions, function (DatabaseEntityInterface $entity): void {
            $this->eventDispatcher->dispatch(new EntityCreatedEvent($entity));
        });

        \array_walk_recursive($entityUpdates, function (DatabaseEntityInterface $entity): void {
            $this->eventDispatcher->dispatch(new EntityUpdatedEvent($entity));
        });
    }

    public function enable(): void
    {
        $this->enabled = true;
    }
}
