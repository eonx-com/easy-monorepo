<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;

final class EntityEventSubscriber implements EntityEventSubscriberInterface
{
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

    public function addAcceptableEntity(string $acceptableEntityClass): void
    {
        if (\in_array($acceptableEntityClass, $this->acceptableEntities, true)) {
            return;
        }

        $this->acceptableEntities[] = $acceptableEntityClass;
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

        foreach ($scheduledEntityInsertions as $object) {
            $changeSet = $unitOfWork->getEntityChangeSet($object);
            $this->eventDispatcher->deferInsert($transactionNestingLevel, $object, $changeSet);
        }

        foreach ($scheduledEntityUpdates as $object) {
            $changeSet = $unitOfWork->getEntityChangeSet($object);
            $this->eventDispatcher->deferUpdate($transactionNestingLevel, $object, $changeSet);
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
}
