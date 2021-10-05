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
    private $entities;

    /**
     * @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param string[] $entities
     */
    public function __construct(DeferredEntityEventDispatcherInterface $eventDispatcher, array $entities)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entities = $entities;
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

        if (\count($scheduledEntityInsertions) > 0) {
            $this->eventDispatcher->deferInsertions($scheduledEntityInsertions, $transactionNestingLevel);
        }

        if (\count($scheduledEntityUpdates) > 0) {
            $this->eventDispatcher->deferUpdates($scheduledEntityUpdates, $transactionNestingLevel);
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
            foreach ($this->entities as $acceptableEntityClass) {
                if (\is_a($entity, $acceptableEntityClass)) {
                    return true;
                }
            }

            return false;
        });
    }
}
