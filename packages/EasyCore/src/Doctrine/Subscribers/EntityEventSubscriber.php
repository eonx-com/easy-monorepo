<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\Subscribers;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcherInterface;

final class EntityEventSubscriber implements EntityEventSubscriberInterface
{
    /**
     * @var string[]
     */
    private $entities;

    /**
     * @var \EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcherInterface
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
        $scheduledEntityInsertions = $unitOfWork->getScheduledEntityInsertions();
        $scheduledEntityUpdates = $unitOfWork->getScheduledEntityUpdates();

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
}
