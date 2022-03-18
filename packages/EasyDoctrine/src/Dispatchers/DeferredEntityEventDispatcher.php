<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Dispatchers;

use EonX\EasyDoctrine\Events\EntityCreatedEvent;
use EonX\EasyDoctrine\Events\EntityDeletedEvent;
use EonX\EasyDoctrine\Events\EntityUpdatedEvent;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use LogicException;

final class DeferredEntityEventDispatcher implements DeferredEntityEventDispatcherInterface
{
    /**
     * @var array<string>
     */
    private $disableList = [];

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
    private $entityDeletions = [];

    /**
     * @var array<string, object>
     */
    private $entityInsertions = [];

    /**
     * @var array<string, object>
     */
    private $entityUpdates = [];

    /**
     * @var \EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->enabled = true;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addToDisableList(array $objectClasses): void
    {
        foreach ($objectClasses as $objectClass) {
            $this->disableList[$objectClass] = $objectClass;
        }
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
        $this->entityDeletions = [];
        $this->entityInsertions = [];
        $this->entityUpdates = [];
    }

    /**
     * @inheritdoc
     */
    public function deferDelete(int $transactionNestingLevel, object $object, array $entityChangeSet): void
    {
        if ($this->isEnabled($object) === false) {
            return;
        }

        $oid = \spl_object_hash($object);
        // `clone` is used to preserve the identifier that is removed after deleting entity
        $this->entityDeletions[$oid] = clone $object;
        $this->entityChangeSets[$transactionNestingLevel][$oid] = $entityChangeSet;
    }

    /**
     * @inheritdoc
     */
    public function deferInsert(int $transactionNestingLevel, object $object, array $entityChangeSet): void
    {
        if ($this->isEnabled($object) === false) {
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
        if ($this->isEnabled($object) === false) {
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
        if ($this->isEnabled() === false) {
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

    public function removeFromDisableList(array $objectClasses): void
    {
        foreach ($objectClasses as $objectClass) {
            if (isset($this->disableList[$objectClass])) {
                unset($this->disableList[$objectClass]);
            }
        }
    }

    /**
     * @param string $oid
     * @param array<string, array{mixed, mixed}> $entityChangeSet
     *
     * @return \EonX\EasyDoctrine\Events\EntityActionEventInterface
     */
    private function createEntityEvent(string $oid, array $entityChangeSet)
    {
        if (isset($this->entityInsertions[$oid]) !== false) {
            return new EntityCreatedEvent($this->entityInsertions[$oid], $entityChangeSet);
        }

        if (isset($this->entityUpdates[$oid]) !== false) {
            return new EntityUpdatedEvent($this->entityUpdates[$oid], $entityChangeSet);
        }

        if (isset($this->entityDeletions[$oid]) !== false) {
            return new EntityDeletedEvent($this->entityDeletions[$oid], $entityChangeSet);
        }

        // @codeCoverageIgnoreStart
        throw new LogicException('Entity for event not found.');
        // @codeCoverageIgnoreEnd
    }

    private function isEnabled(?object $object = null): bool
    {
        if ($object === null) {
            return $this->enabled;
        }

        return $this->enabled === true && isset($this->disableList[\get_class($object)]) === false;
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
