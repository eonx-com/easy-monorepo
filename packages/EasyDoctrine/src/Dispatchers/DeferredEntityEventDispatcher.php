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
    private array $collectionChangeSets = [];

    /**
     * @var array<int, object>
     */
    private array $createdEntities = [];

    /**
     * @var array<int, object>
     */
    private array $deletedEntities = [];

    private bool $enabled;

    private array $entityChangeSets = [];

    /**
     * @var array<int, object>
     */
    private array $updatedEntities = [];

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectCopierInterface $objectCopier,
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

            foreach (\array_keys($this->collectionChangeSets) as $level) {
                if ($level >= $transactionNestingLevel) {
                    $this->collectionChangeSets[$level] = [];
                }
            }

            $activeEntityHashes = [];

            foreach ($this->entityChangeSets as $levelEntityChangeSets) {
                foreach ($levelEntityChangeSets as $entityHash => $changeSet) {
                    $activeEntityHashes[$entityHash] = true;
                }
            }

            foreach ($this->collectionChangeSets as $levelEntityChangeSets) {
                foreach ($levelEntityChangeSets as $entityHash => $changeSet) {
                    $activeEntityHashes[$entityHash] = true;
                }
            }

            foreach ($this->createdEntities as $entityHash => $value) {
                if (isset($activeEntityHashes[$entityHash]) === false) {
                    unset($this->createdEntities[$entityHash]);
                }
            }

            foreach ($this->updatedEntities as $entityHash => $value) {
                if (isset($activeEntityHashes[$entityHash]) === false) {
                    unset($this->updatedEntities[$entityHash]);
                }
            }

            return;
        }

        $this->entityChangeSets = [];
        $this->collectionChangeSets = [];
        $this->deletedEntities = [];
        $this->createdEntities = [];
        $this->updatedEntities = [];
    }

    public function deferCollectionUpdate(
        int $transactionNestingLevel,
        object $entity,
        string $fieldName,
        array $oldIds,
        array $newsIds,
    ): void {
        if ($this->enabled === false) {
            return;
        }

        $entityObjectId = \spl_object_id($entity);
        $this->updatedEntities[$entityObjectId] = $entity;
        $this->collectionChangeSets[$transactionNestingLevel][$entityObjectId][$fieldName] = [
            'new' => $newsIds,
            'old' => $oldIds,
        ];
    }

    public function deferDelete(int $transactionNestingLevel, object $entity, array $entityChangeSet): void
    {
        if ($this->enabled === false) {
            return;
        }

        $entityObjectId = \spl_object_id($entity);
        // \EonX\EasyDoctrine\Interfaces\ObjectCopierInterface is used to preserve the identifier that is removed after deleting entity
        $this->deletedEntities[$entityObjectId] = $this->objectCopier->copy($entity);
        $this->entityChangeSets[$transactionNestingLevel][$entityObjectId] = $entityChangeSet;
    }

    public function deferInsert(int $transactionNestingLevel, object $entity, array $entityChangeSet): void
    {
        if ($this->enabled === false) {
            return;
        }

        $entityObjectId = \spl_object_id($entity);
        $this->createdEntities[$entityObjectId] = $entity;
        $this->entityChangeSets[$transactionNestingLevel][$entityObjectId] = $entityChangeSet;
    }

    public function deferUpdate(int $transactionNestingLevel, object $entity, array $entityChangeSet): void
    {
        if ($this->enabled === false) {
            return;
        }

        $entityObjectId = \spl_object_id($entity);
        $this->updatedEntities[$entityObjectId] = $entity;
        $this->entityChangeSets[$transactionNestingLevel][$entityObjectId] = $this->mergeChangeSet(
            $this->entityChangeSets[$transactionNestingLevel][$entityObjectId] ?? [],
            $entityChangeSet
        );
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
                foreach ($levelChangeSets as $entityObjectId => $changeSet) {
                    $mergedEntityChangeSets[$entityObjectId] = $this->mergeChangeSet(
                        $mergedEntityChangeSets[$entityObjectId] ?? [],
                        $changeSet
                    );
                }
            }

            foreach ($this->collectionChangeSets as $levelChangeSets) {
                foreach ($levelChangeSets as $entityObjectId => $entityCollectionChangeSets) {
                    foreach ($entityCollectionChangeSets as $associationName => $changeSet) {
                        $computedChangeSet = [[], []];

                        foreach ($changeSet['old'] as $id) {
                            $computedChangeSet[0][] = \is_callable($id) ? $id() : $id;
                        }

                        foreach ($changeSet['new'] as $id) {
                            $computedChangeSet[1][] = \is_callable($id) ? $id() : $id;
                        }

                        $mergedEntityChangeSets[$entityObjectId] = $this->mergeChangeSet(
                            $mergedEntityChangeSets[$entityObjectId] ?? [],
                            [$associationName => $computedChangeSet]
                        );
                    }
                }
            }

            /** @var int $entityObjectId */
            foreach ($mergedEntityChangeSets as $entityObjectId => $entityChangeSet) {
                $event = $this->createEntityEvent($entityObjectId, $entityChangeSet);

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

    private function createEntityEvent(int $entityObjectId, array $entityChangeSet): EntityActionEventInterface
    {
        if (isset($this->createdEntities[$entityObjectId]) !== false) {
            return new EntityCreatedEvent($this->createdEntities[$entityObjectId], $entityChangeSet);
        }

        if (isset($this->updatedEntities[$entityObjectId]) !== false) {
            return new EntityUpdatedEvent($this->updatedEntities[$entityObjectId], $entityChangeSet);
        }

        if (isset($this->deletedEntities[$entityObjectId]) !== false) {
            return new EntityDeletedEvent($this->deletedEntities[$entityObjectId], $entityChangeSet);
        }

        // @codeCoverageIgnoreStart
        throw new LogicException('Entity for event not found.');
        // @codeCoverageIgnoreEnd
    }

    private function mergeChangeSet(array $currentChangeSet, array $newChangeSet): array
    {
        foreach ($newChangeSet as $key => [$old, $new]) {
            if (isset($currentChangeSet[$key]) === false) {
                $currentChangeSet[$key] = [$old, $new];

                continue;
            }

            $currentChangeSet[$key][1] = $new;
        }

        return $currentChangeSet;
    }
}
