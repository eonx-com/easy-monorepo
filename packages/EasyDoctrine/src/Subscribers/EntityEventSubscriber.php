<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Interfaces\EntityEventSubscriberInterface;
use SplObjectStorage;
use Stringable;

final class EntityEventSubscriber implements EntityEventSubscriberInterface
{
    private const DATETIME_COMPARISON_FORMAT = 'Y-m-d H:i:s.uP';

    /**
     * @var string[]
     */
    private array $acceptableEntities;

    /**
     * @param string[] $entities
     */
    public function __construct(
        private DeferredEntityEventDispatcherInterface $eventDispatcher,
        array $entities,
    ) {
        $this->acceptableEntities = $entities;
    }

    /**
     * @param list<\Doctrine\ORM\PersistentCollection<TKey, T>> $collections
     *
     * @return array<string, array<mixed>>
     *
     * @template TKey of array-key
     * @template T
     */
    public function computeCollectionsChangeSet(
        array $collections,
        EntityManagerInterface $entityManager,
    ): array {
        $changeSet = [];
        $mappingIdsFunction = static function (object $entity) use ($entityManager): string {
            $identifierName = \current($entityManager->getClassMetadata($entity::class)->getIdentifier());

            return (string)$entityManager->getUnitOfWork()
                ->getEntityIdentifier($entity)[$identifierName];
        };
        foreach ($collections as $collection) {
            $snapshotIds = \array_map($mappingIdsFunction, $collection->getSnapshot());
            $actualIds = \array_map($mappingIdsFunction, $collection->toArray());
            $diff = \array_diff($snapshotIds, $actualIds);
            if (\count($diff) > 0 || \count($snapshotIds) !== \count($actualIds)) {
                /** @var array{fieldName: string} $mapping */
                $mapping = $collection->getMapping();
                $changeSet[$mapping['fieldName']] = [\array_values($snapshotIds), \array_values($actualIds)];
            }
        }

        return $changeSet;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::onFlush, Events::postFlush];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $transactionNestingLevel = $entityManager->getConnection()
            ->getTransactionNestingLevel();
        $scheduledEntityInsertions = $this->filterEntities($unitOfWork->getScheduledEntityInsertions());
        $scheduledEntityUpdates = $this->filterEntities($unitOfWork->getScheduledEntityUpdates());
        $scheduledEntityDeletions = $this->filterEntities($unitOfWork->getScheduledEntityDeletions());
        $scheduledCollectionUpdates = $this->filterCollections($unitOfWork->getScheduledCollectionUpdates());

        $collectionsMapping = new SplObjectStorage();
        foreach ($scheduledCollectionUpdates as $collection) {
            /** @var object $owner */
            $owner = $collection->getOwner();
            if ($collectionsMapping->contains($owner) === false) {
                $collectionsMapping->offsetSet($owner, []);
            }
            $collections = $collectionsMapping->offsetGet($owner);
            $collections[] = $collection;
            $collectionsMapping->offsetSet($owner, $collections);
        }

        foreach ($scheduledEntityInsertions as $object) {
            /** @var array<string, array{mixed, mixed}> $changeSet */
            $changeSet = $unitOfWork->getEntityChangeSet($object);

            if ($collectionsMapping->contains($object)) {
                $collectionsChangeSet = $this->computeCollectionsChangeSet(
                    $collectionsMapping->offsetGet($object),
                    $entityManager
                );
                $collectionsMapping->detach($object);
                /** @var array<string, array{mixed, mixed}> $changeSet */
                $changeSet = [...$changeSet, ...$collectionsChangeSet];
            }

            $this->eventDispatcher->deferInsert($transactionNestingLevel, $object, $changeSet);
        }

        foreach ($scheduledEntityUpdates as $object) {
            /** @var array<string, array{mixed, mixed}> $changeSet */
            $changeSet = $this->getClearedChangeSet($unitOfWork->getEntityChangeSet($object));
            if ($collectionsMapping->contains($object)) {
                $collectionsChangeSet = $this->computeCollectionsChangeSet(
                    $collectionsMapping->offsetGet($object),
                    $entityManager
                );
                $collectionsMapping->detach($object);
                /** @var array<string, array{mixed, mixed}> $changeSet */
                $changeSet = [...$changeSet, ...$collectionsChangeSet];
            }

            if (\count($changeSet) > 0) {
                $this->eventDispatcher->deferUpdate($transactionNestingLevel, $object, $changeSet);
            }
        }

        foreach ($scheduledEntityDeletions as $object) {
            /** @var array<string, array{mixed, mixed}> $changeSet */
            $changeSet = [];
            $originalEntityData = $unitOfWork->getOriginalEntityData($object);
            foreach ($originalEntityData as $attribute => $value) {
                $changeSet[$attribute] = [$value, null];
            }
            $this->eventDispatcher->deferDelete($transactionNestingLevel, $object, $changeSet);
        }

        $collectionsMapping->rewind();
        foreach ($collectionsMapping as $object) {
            /** @var array<string, array{mixed, mixed}> $collectionsChangeSet */
            $collectionsChangeSet = $this->computeCollectionsChangeSet(
                $collectionsMapping->offsetGet($object),
                $entityManager
            );
            $this->eventDispatcher->deferUpdate($transactionNestingLevel, $object, $collectionsChangeSet);
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getEntityManager();

        if ($entityManager->getConnection()->getTransactionNestingLevel() === 0) {
            $this->eventDispatcher->dispatch();
        }
    }

    /**
     * @param list<\Doctrine\ORM\PersistentCollection<TKey, T>> $collections
     *
     * @return list<\Doctrine\ORM\PersistentCollection<TKey, T>>
     *
     * @template TKey of array-key
     * @template T
     */
    private function filterCollections(array $collections): array
    {
        return \array_filter($collections, function (PersistentCollection $collection): bool {
            $typeClass = $collection->getTypeClass();
            if ($typeClass->idGenerator->isPostInsertGenerator()) {
                return false;
            }

            /** @var object $owner */
            $owner = $collection->getOwner();
            foreach ($this->acceptableEntities as $acceptableEntityClass) {
                if (\is_a($owner, $acceptableEntityClass)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @param object[] $entities
     *
     * @return object[]
     */
    private function filterEntities(array $entities): array
    {
        return \array_filter($entities, function (object $entity): bool {
            foreach ($this->acceptableEntities as $acceptableEntityClass) {
                if (\is_a($entity, $acceptableEntityClass)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @param mixed[] $changeSet
     *
     * @return mixed[]
     */
    private function getClearedChangeSet(array $changeSet): array
    {
        return \array_filter($changeSet, static function (array|PersistentCollection $changeSetItem): bool {
            if (($changeSetItem[0] ?? null) instanceof DateTimeInterface &&
                ($changeSetItem[1] ?? null) instanceof DateTimeInterface) {
                return $changeSetItem[0]->format(self::DATETIME_COMPARISON_FORMAT) !==
                    $changeSetItem[1]->format(self::DATETIME_COMPARISON_FORMAT);
            }

            if (($changeSetItem[0] ?? null) instanceof Stringable &&
                ($changeSetItem[1] ?? null) instanceof Stringable) {
                return (string)$changeSetItem[0] !== (string)$changeSetItem[1];
            }

            return true;
        });
    }
}
