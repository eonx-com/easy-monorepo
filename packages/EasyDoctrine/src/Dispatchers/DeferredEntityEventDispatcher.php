<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Dispatchers;

use EonX\EasyDoctrine\Events\EntityActionEventInterface;
use EonX\EasyDoctrine\Events\EntityCreatedEvent;
use EonX\EasyDoctrine\Events\EntityDeletedEvent;
use EonX\EasyDoctrine\Events\EntityUpdatedEvent;
use EonX\EasyDoctrine\Interfaces\ObjectCopierInterface;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use LogicException;

final class DeferredEntityEventDispatcher implements DeferredEntityEventDispatcherInterface
{
    private bool $enabled;

    private array $entityChangeSets = [];

    /**
     * @var array<string, object>
     */
    private array $entityDeletions = [];

    /**
     * @var array<string, object>
     */
    private array $entityInsertions = [];

    /**
     * @var array<string, object>
     */
    private array $entityUpdates = [];

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ObjectCopierInterface $objectCopier,
    ) {
        $this->enabled = true;
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
        if ($this->enabled === false) {
            return;
        }

        $oid = \spl_object_hash($object);
        // `clone` is used to preserve the identifier that is removed after deleting entity
        $this->entityDeletions[$oid] = $this->objectCopier->copy($object);
        $this->entityChangeSets[$transactionNestingLevel][$oid] = $entityChangeSet;
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

        $events = [];

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

            /**
             * @var string $oid
             * @var array $entityChangeSet
             */
            foreach ($mergedEntityChangeSets as $oid => $entityChangeSet) {
                $event = $this->createEntityEvent($oid, $entityChangeSet);

                $events[] = $event;
            }
        } finally {
            $this->clear();
        }

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    private function createEntityEvent(string $oid, array $entityChangeSet): EntityActionEventInterface
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
