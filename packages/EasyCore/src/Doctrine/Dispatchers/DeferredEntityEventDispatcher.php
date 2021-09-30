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
     * @var array<int, array<string, array<string, array{mixed, mixed}>>>
     */
    private $entityChangeSets = [];

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

    /**
     * @param int $transactionNestingLevel
     * @param string $oid
     * @param array<string, array{mixed, mixed}> $entityChangeSet
     */
    public function addEntityChangeSet(int $transactionNestingLevel, string $oid, array $entityChangeSet): void
    {
        $this->entityChangeSets[$transactionNestingLevel][$oid] = \array_merge(
            $this->entityChangeSets[$transactionNestingLevel][$oid] ?? [],
            $entityChangeSet
        );
    }

    public function clear(?int $transactionNestingLevel = null): void
    {
        if ($transactionNestingLevel !== null) {
            foreach (\array_keys($this->entityChangeSets) as $level) {
                if ($level >= $transactionNestingLevel) {
                    $this->entityChangeSets[$level] = [];
                }
            }

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

        $this->entityChangeSets = [];
        $this->entityInsertions = [];
        $this->entityUpdates = [];
    }

    public function deferInsert(int $transactionNestingLevel, string $oid, object $entity): void
    {
        if ($this->enabled === false) {
            return;
        }

        $this->entityInsertions[$transactionNestingLevel][$oid] = $entity;
    }

    public function deferUpdate(int $transactionNestingLevel, string $oid, object $entity): void
    {
        if ($this->enabled === false) {
            return;
        }

        $this->entityUpdates[$transactionNestingLevel][$oid] = $entity;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function dispatch(): void
    {
        $entityInsertions = $this->entityInsertions;
        $entityUpdates = $this->entityUpdates;
        $entityChangeSets = $this->entityChangeSets;

        $this->clear();

        if ($this->enabled === false) {
            return;
        }

        $mergedEntityChangeSets = [];
        foreach ($entityChangeSets as $levelChangeSets) {
            foreach ($levelChangeSets as $oid => $changeSet) {
                $mergedEntityChangeSets[$oid] = $this->mergeChangeSet(
                    $mergedEntityChangeSets[$oid] ?? [],
                    $changeSet
                );
            }
        }

        $processedEntities = [];
        foreach ($entityInsertions as $entities) {
            foreach ($entities as $oid => $entity) {
                $processedEntities[$oid] = $entity;
                $this->eventDispatcher->dispatch(
                    new EntityCreatedEvent($entity, $mergedEntityChangeSets[$oid] ?? [])
                );
            }
        }

        foreach ($entityUpdates as $entities) {
            foreach ($entities as $oid => $entity) {
                if (isset($processedEntities[$oid])) {
                    continue;
                }
                $processedEntities[$oid] = $entity;
                $this->eventDispatcher->dispatch(
                    new EntityUpdatedEvent($entity, $mergedEntityChangeSets[$oid] ?? [])
                );
            }
        }
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * @param array<string, array{mixed, mixed}> $array1
     * @param array<string, array{mixed, mixed}> $array2
     *
     * @return array<string, array{mixed, mixed}>
     */
    private function mergeChangeSet(array $array1, array $array2): array
    {
        foreach ($array2 as $key => [$old, $new]) {
            if (isset($array1[$key]) === false) {
                $array1[$key] = [$old, $new];
                continue;
            }
            $array1[$key][1] = $new;
        }

        return $array1;
    }
}
