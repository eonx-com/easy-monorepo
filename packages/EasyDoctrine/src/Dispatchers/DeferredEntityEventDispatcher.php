<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Dispatchers;

use EonX\EasyDoctrine\Events\EntityCreatedEvent;
use EonX\EasyDoctrine\Events\EntityUpdatedEvent;
use LogicException;
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
     * @var array<string, object>
     */
    private $entityInsertions = [];

    /**
     * @var array<string, object>
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
            foreach (\array_keys($this->entityChangeSets) as $level) {
                if ($level >= $transactionNestingLevel) {
                    $this->entityChangeSets[$level] = [];
                }
            }

            $activeOids = [];
            foreach ($this->entityChangeSets as $levelEntityChangeSets) {
                foreach ($levelEntityChangeSets as $oid => $changeSet) {
                    $activeOids[$oid] = true;
                }
            }

            foreach ($this->entityInsertions as $oid => $value) {
                if (isset($activeOids[$oid]) === false) {
                    unset($this->entityInsertions[$oid]);
                }
            }

            foreach ($this->entityUpdates as $oid => $value) {
                if (isset($activeOids[$oid]) === false) {
                    unset($this->entityUpdates[$oid]);
                }
            }

            return;
        }

        $this->entityChangeSets = [];
        $this->entityInsertions = [];
        $this->entityUpdates = [];
    }

    /**
     * @inheritdoc
     */
    public function deferInsert(int $transactionNestingLevel, object $object, array $entityChangeSet): void
    {
        if ($this->enabled === false) {
            return;
        }

        $oid = \spl_object_hash($object);
        $this->entityInsertions[$oid] = $object;
        $this->entityChangeSets[$transactionNestingLevel][$oid] = $entityChangeSet;
    }

    /**
     * @inheritdoc
     */
    public function deferUpdate(int $transactionNestingLevel, object $object, array $entityChangeSet): void
    {
        if ($this->enabled === false) {
            return;
        }

        $oid = \spl_object_hash($object);
        $this->entityUpdates[$oid] = $object;
        $this->entityChangeSets[$transactionNestingLevel][$oid] = $entityChangeSet;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function dispatch(): void
    {
        if ($this->enabled === false) {
            return;
        }

        try {
            $mergedEntityChangeSets = [];
            foreach ($this->entityChangeSets as $levelChangeSets) {
                foreach ($levelChangeSets as $oid => $changeSet) {
                    $mergedEntityChangeSets[$oid] = $this->mergeChangeSet(
                        $mergedEntityChangeSets[$oid] ?? [],
                        $changeSet
                    );
                }
            }

            foreach ($mergedEntityChangeSets as $oid => $entityChangeSet) {
                $event = $this->createEntityEvent($oid, $entityChangeSet);

                $this->eventDispatcher->dispatch($event);
            }
        } finally {
            $this->clear();
        }
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * @param string $oid
     * @param array<string, array{mixed, mixed}> $entityChangeSet
     *
     * @return \EonX\EasyDoctrine\Events\EntityCreatedEvent|\EonX\EasyDoctrine\Events\EntityUpdatedEvent
     */
    private function createEntityEvent(string $oid, array $entityChangeSet)
    {
        if (isset($this->entityInsertions[$oid]) !== false) {
            return new EntityCreatedEvent($this->entityInsertions[$oid], $entityChangeSet);
        }

        if (isset($this->entityUpdates[$oid]) !== false) {
            return new EntityUpdatedEvent($this->entityUpdates[$oid], $entityChangeSet);
        }

        // @codeCoverageIgnoreStart
        throw new LogicException('Entity for event not found.');
        // @codeCoverageIgnoreEnd
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
