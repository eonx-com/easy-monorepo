<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use DateTimeInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Interfaces\EntityEventSubscriberInterface;

final class EntityEventSubscriber implements EntityEventSubscriberInterface
{
    /**
     * @var string
     */
    private const DATETIME_COMPARISON_FORMAT = 'Y-m-d H:i:s.uP';

    /**
     * @var string[]
     */
    private $acceptableEntities;

    /**
     * @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param string[] $entities
     */
    public function __construct(
        DeferredEntityEventDispatcherInterface $eventDispatcher,
        array $entities
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->acceptableEntities = $entities;
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

        foreach ($scheduledEntityInsertions as $object) {
            /** @var array<string, array{mixed, mixed}> $changeSet */
            $changeSet = $unitOfWork->getEntityChangeSet($object);
            $this->eventDispatcher->deferInsert($transactionNestingLevel, $object, $changeSet);
        }

        foreach ($scheduledEntityUpdates as $object) {
            /** @var array<string, array{mixed, mixed}> $changeSet */
            $changeSet = $unitOfWork->getEntityChangeSet($object);
            $changeSet = $this->getClearedChangeSet($changeSet);
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
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $entityManager = $eventArgs->getEntityManager();

        if ($entityManager->getConnection()->getTransactionNestingLevel() === 0) {
            $this->eventDispatcher->dispatch();
        }
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
        return \array_filter($changeSet, static function (array|PersistentCollection $changeSetItem) {
            if (($changeSetItem[0] ?? null) instanceof DateTimeInterface &&
                ($changeSetItem[1] ?? null) instanceof DateTimeInterface) {
                return $changeSetItem[0]->format(self::DATETIME_COMPARISON_FORMAT) !==
                    $changeSetItem[1]->format(self::DATETIME_COMPARISON_FORMAT);
            }

            return true;
        });
    }
}
