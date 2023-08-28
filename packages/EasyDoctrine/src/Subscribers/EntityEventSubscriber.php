<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use DateTimeInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Interfaces\EntityEventSubscriberInterface;
use InvalidArgumentException;
use ReflectionProperty;
use Stringable;

final class EntityEventSubscriber implements EntityEventSubscriberInterface
{
    private const DATETIME_COMPARISON_FORMAT = 'Y-m-d H:i:s.uP';

    /**
     * @var class-string[] $subscribedEntities
     */
    private array $subscribedEntities;

    /**
     * @param class-string[]|null $entities
     * @param class-string[]|null $subscribedEntities
     */
    public function __construct(
        private readonly DeferredEntityEventDispatcherInterface $eventDispatcher,
        // @deprecated Since 4.0, will be removed in 6.0. Use $subscribedEntities instead
        ?array $entities = null,
        ?array $subscribedEntities = null,
    ) {
        $this->subscribedEntities = $entities ?? $subscribedEntities ?? throw new InvalidArgumentException(
            'You must provide at least one entity to subscribe to'
        );
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
            Events::postFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $transactionNestingLevel = $entityManager->getConnection()
            ->getTransactionNestingLevel();

        $this->prepareDeferredDeletions($transactionNestingLevel, $unitOfWork);

        $this->prepareDeferredInsertions($transactionNestingLevel, $unitOfWork);

        $this->prepareDeferredUpdates($transactionNestingLevel, $unitOfWork);

        $this->prepareDeferredCollectionUpdates($transactionNestingLevel, $unitOfWork);
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getEntityManager();

        if ($entityManager->getConnection()->getTransactionNestingLevel() === 0) {
            $this->eventDispatcher->dispatch();
        }
    }

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

    private function isEntitySubscribed(object $entity): bool
    {
        foreach ($this->subscribedEntities as $subscribedEntity) {
            if (\is_a($entity, $subscribedEntity)) {
                return true;
            }
        }

        return false;
    }

    private function prepareDeferredCollectionUpdates(int $transactionNestingLevel, UnitOfWork $unitOfWork): void
    {
        $scheduledCollectionUpdates = [];
        /** @var \Doctrine\ORM\PersistentCollection<int, object> $collection */
        foreach ($unitOfWork->getScheduledCollectionUpdates() as $collection) {
            if ($collection->getOwner() !== null && $this->isEntitySubscribed($collection->getOwner())) {
                $scheduledCollectionUpdates[\spl_object_id($collection)] = $collection;
            }
        }

        // Handle collection deletions when ManyToMany is the owning side
        // See https://github.com/doctrine/orm/pull/10763
        if (\property_exists($unitOfWork, 'pendingCollectionElementRemovals')) {
            $pendingCollectionElementRemovalsReflection = new ReflectionProperty(
                $unitOfWork::class,
                'pendingCollectionElementRemovals'
            );
            $pendingCollectionElementRemovals = $pendingCollectionElementRemovalsReflection->getValue($unitOfWork);

            $visitedCollectionsReflection = new ReflectionProperty(
                $unitOfWork::class,
                'visitedCollections'
            );
            $visitedCollections = $visitedCollectionsReflection->getValue($unitOfWork);

            foreach (\array_keys($pendingCollectionElementRemovals) as $collectionObjectId) {
                if (isset($scheduledCollectionUpdates[$collectionObjectId])) {
                    continue;
                }

                $collection = $visitedCollections[$collectionObjectId];
                if ($collection->getOwner() !== null && $this->isEntitySubscribed($collection->getOwner())) {
                    $scheduledCollectionUpdates[$collectionObjectId] = $collection;
                }
            }
        }

        foreach ($scheduledCollectionUpdates as $collectionObjectId => $collection) {
            $snapshotIds = [];
            foreach ($collection->getSnapshot() as $entity) {
                $snapshotIds[] = $unitOfWork->getSingleIdentifierValue($entity)
                    ?? static fn (): mixed => $unitOfWork->getSingleIdentifierValue($entity);
            }

            $actualIds = [];
            foreach ($collection->toArray() as $key => $entity) {
                if (isset($pendingCollectionElementRemovals[$collectionObjectId][$key])) {
                    continue;
                }

                $actualIds[] = $unitOfWork->getSingleIdentifierValue($entity)
                    ?? static fn (): mixed => $unitOfWork->getSingleIdentifierValue($entity);
            }

            $diff = \array_udiff($snapshotIds, $actualIds, static function (mixed $a, mixed $b): int {
                $a = \is_callable($a) ? $a() : $a;
                $b = \is_callable($b) ? $b() : $b;

                return $a <=> $b;
            });

            if (\count($diff) > 0 || \count($snapshotIds) !== \count($actualIds)) {
                $mapping = $collection->getMapping();

                $this->eventDispatcher->deferCollectionUpdate(
                    $transactionNestingLevel,
                    $collection->getOwner(),
                    $mapping['fieldName'],
                    $snapshotIds,
                    $actualIds
                );
            }
        }

        /** @var \Doctrine\ORM\PersistentCollection<int, object> $collection */
        foreach ($unitOfWork->getScheduledCollectionDeletions() as $collection) {
            if ($collection->getOwner() !== null && $this->isEntitySubscribed($collection->getOwner())) {
                /** @var array{fieldName: string} $mapping */
                $mapping = $collection->getMapping();

                $this->eventDispatcher->deferCollectionUpdate(
                    $transactionNestingLevel,
                    $collection->getOwner(),
                    $mapping['fieldName'],
                    ['Not available'],
                    ['Collection was cleared']
                );
            }
        }
    }

    private function prepareDeferredDeletions(int $transactionNestingLevel, UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if ($this->isEntitySubscribed($entity)) {
                $changeSet = [];
                foreach ($unitOfWork->getOriginalEntityData($entity) as $attribute => $value) {
                    $changeSet[$attribute] = [$value, null];
                }

                $this->eventDispatcher->deferDelete($transactionNestingLevel, $entity, $changeSet);
            }
        }
    }

    private function prepareDeferredInsertions(int $transactionNestingLevel, UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($this->isEntitySubscribed($entity)) {
                $changeSet = $unitOfWork->getEntityChangeSet($entity);
                $this->eventDispatcher->deferInsert($transactionNestingLevel, $entity, $changeSet);
            }
        }
    }

    private function prepareDeferredUpdates(int $transactionNestingLevel, UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($this->isEntitySubscribed($entity)) {
                $changeSet = $this->getClearedChangeSet($unitOfWork->getEntityChangeSet($entity));

                if (\count($changeSet) > 0) {
                    $this->eventDispatcher->deferUpdate($transactionNestingLevel, $entity, $changeSet);
                }
            }
        }
    }
}
