<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Listener;

use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;
use InvalidArgumentException;
use ReflectionProperty;
use Stringable;

#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
final readonly class EntityEventListener
{
    private const string DATETIME_COMPARISON_FORMAT = 'Y-m-d H:i:s.uP';

    /**
     * @var class-string[] $trackableEntities
     */
    private array $trackableEntities;

    /**
     * @param class-string[]|null $trackableEntities
     */
    public function __construct(
        private DeferredEntityEventDispatcherInterface $eventDispatcher,
        ?array $trackableEntities = null,
    ) {
        $this->trackableEntities = $trackableEntities ?? throw new InvalidArgumentException(
            'You must provide at least one trackable entity.'
        );
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $objectManager = $eventArgs->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();
        $transactionNestingLevel = $objectManager->getConnection()
            ->getTransactionNestingLevel();

        $this->prepareDeferredDeletions($transactionNestingLevel, $unitOfWork);

        $this->prepareDeferredInsertions($transactionNestingLevel, $unitOfWork);

        $this->prepareDeferredUpdates($transactionNestingLevel, $unitOfWork);

        $this->prepareDeferredCollectionUpdates($transactionNestingLevel, $unitOfWork);
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $objectManager = $eventArgs->getObjectManager();

        if ($objectManager->getConnection()->getTransactionNestingLevel() === 0) {
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

    private function isEntityTrackable(object $entity): bool
    {
        return \array_any(
            $this->trackableEntities,
            static fn ($trackableEntity): bool => \is_a($entity, $trackableEntity)
        );
    }

    private function prepareDeferredCollectionUpdates(int $transactionNestingLevel, UnitOfWork $unitOfWork): void
    {
        $scheduledCollectionUpdates = [];
        /** @var \Doctrine\ORM\PersistentCollection<int, object> $collection */
        foreach ($unitOfWork->getScheduledCollectionUpdates() as $collection) {
            if ($collection->getOwner() !== null && $this->isEntityTrackable($collection->getOwner())) {
                $scheduledCollectionUpdates[\spl_object_id($collection)] = $collection;
            }
        }

        $pendingCollectionElementRemovalsReflection = new ReflectionProperty(
            $unitOfWork::class,
            'pendingCollectionElementRemovals'
        );
        /** @var array $pendingCollectionElementRemovals */
        $pendingCollectionElementRemovals = $pendingCollectionElementRemovalsReflection->getValue($unitOfWork);

        $visitedCollectionsReflection = new ReflectionProperty(
            $unitOfWork::class,
            'visitedCollections'
        );
        /** @var array<\Doctrine\ORM\PersistentCollection<int, object>> $visitedCollections */
        $visitedCollections = $visitedCollectionsReflection->getValue($unitOfWork);

        foreach (\array_keys($pendingCollectionElementRemovals) as $collectionObjectId) {
            if (isset($scheduledCollectionUpdates[$collectionObjectId])) {
                continue;
            }

            $collection = $visitedCollections[$collectionObjectId];
            if ($collection->getOwner() !== null && $this->isEntityTrackable($collection->getOwner())) {
                $scheduledCollectionUpdates[$collectionObjectId] = $collection;
            }
        }

        /** @var \Doctrine\ORM\PersistentCollection<int, object> $collection */
        foreach ($scheduledCollectionUpdates as $collectionObjectId => $collection) {
            $snapshotIds = [];
            /** @var object $entity */
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
                /** @var object $owner */
                $owner = $collection->getOwner();

                $this->eventDispatcher->deferCollectionUpdate(
                    $transactionNestingLevel,
                    $owner,
                    $mapping['fieldName'],
                    $snapshotIds,
                    $actualIds
                );
            }
        }

        /** @var \Doctrine\ORM\PersistentCollection<int, object> $collection */
        foreach ($unitOfWork->getScheduledCollectionDeletions() as $collection) {
            if ($collection->getOwner() !== null && $this->isEntityTrackable($collection->getOwner())) {
                /** @var array{fieldName: string} $mapping */
                $mapping = $collection->getMapping();
                /** @var object $owner */
                $owner = $collection->getOwner();

                $this->eventDispatcher->deferCollectionUpdate(
                    $transactionNestingLevel,
                    $owner,
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
            if ($this->isEntityTrackable($entity)) {
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
            if ($this->isEntityTrackable($entity)) {
                $changeSet = $unitOfWork->getEntityChangeSet($entity);
                $this->eventDispatcher->deferInsert($transactionNestingLevel, $entity, $changeSet);
            }
        }
    }

    private function prepareDeferredUpdates(int $transactionNestingLevel, UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($this->isEntityTrackable($entity)) {
                $changeSet = $this->getClearedChangeSet($unitOfWork->getEntityChangeSet($entity));

                if (\count($changeSet) > 0) {
                    $this->eventDispatcher->deferUpdate($transactionNestingLevel, $entity, $changeSet);
                }
            }
        }
    }
}
