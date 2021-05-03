<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Subscribers;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyCore\Doctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyCore\Interfaces\DatabaseEntityInterface;
use EonX\EasyCore\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyCore\Doctrine\Subscribers\EntityEventSubscriber
 */
final class EntityEventSubscriberTest extends AbstractTestCase
{
    public function testGetSubscribedEventsSucceeds(): void
    {
        $entityEventSubscriber = new EntityEventSubscriber(
            $this->prophesize(DeferredEntityEventDispatcherInterface::class)->reveal(),
            []
        );

        $subscribedEvents = $entityEventSubscriber->getSubscribedEvents();

        self::assertSame([Events::onFlush, Events::postFlush], $subscribedEvents);
    }

    public function testOnFlushSucceeds(): void
    {
        $newEntity = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $existedEntity = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $notAcceptableEntity = $this->prophesize(stdClass::class)
            ->willImplement(DatabaseEntityInterface::class)
            ->reveal();
        $unitOfWork = $this->prophesize(UnitOfWork::class);
        $unitOfWork->getScheduledEntityInsertions()
            ->willReturn([$newEntity, $notAcceptableEntity]);
        $unitOfWork->getScheduledEntityUpdates()
            ->willReturn([$existedEntity, $notAcceptableEntity]);
        $connection = $this->prophesize(Connection::class);
        $connection->getTransactionNestingLevel()
            ->willReturn(0);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getUnitOfWork()
            ->willReturn($unitOfWork->reveal());
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        $eventArgs = $this->prophesize(OnFlushEventArgs::class);
        $eventArgs->getEntityManager()
            ->willReturn($entityManager->reveal());
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        $entityEventSubscriber = new EntityEventSubscriber(
            $deferredEntityEventDispatcher->reveal(),
            [\get_class($newEntity), \get_class($existedEntity)]
        );

        $entityEventSubscriber->onFlush($eventArgs->reveal());

        $eventArgs->getEntityManager()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getUnitOfWork()
            ->shouldHaveBeenCalledOnce();
        $unitOfWork->getScheduledEntityInsertions()
            ->shouldHaveBeenCalledOnce();
        $unitOfWork->getScheduledEntityUpdates()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->deferInsertions([$newEntity], 0)->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->deferUpdates([$existedEntity], 0)->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
    }

    public function testPostFlushSucceedsAndDispatchesEvents(): void
    {
        $connection = $this->prophesize(Connection::class);
        $connection->getTransactionNestingLevel()
            ->willReturn(0);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        $eventArgs = $this->prophesize(PostFlushEventArgs::class);
        $eventArgs->getEntityManager()
            ->willReturn($entityManager->reveal());
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        $entityEventSubscriber = new EntityEventSubscriber($deferredEntityEventDispatcher->reveal(), []);

        $entityEventSubscriber->postFlush($eventArgs->reveal());

        $eventArgs->getEntityManager()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->dispatch()
            ->shouldHaveBeenCalledOnce();
    }

    public function testPostFlushSucceedsAndDoesNotDispatchEvents(): void
    {
        $connection = $this->prophesize(Connection::class);
        $connection->getTransactionNestingLevel()
            ->willReturn(1);
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getConnection()
            ->willReturn($connection->reveal());
        $eventArgs = $this->prophesize(PostFlushEventArgs::class);
        $eventArgs->getEntityManager()
            ->willReturn($entityManager->reveal());
        $deferredEntityEventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class);
        $entityEventSubscriber = new EntityEventSubscriber($deferredEntityEventDispatcher->reveal(), []);

        $entityEventSubscriber->postFlush($eventArgs->reveal());

        $eventArgs->getEntityManager()
            ->shouldHaveBeenCalledOnce();
        $entityManager->getConnection()
            ->shouldHaveBeenCalledOnce();
        $connection->getTransactionNestingLevel()
            ->shouldHaveBeenCalledOnce();
        $deferredEntityEventDispatcher->dispatch()
            ->shouldNotHaveBeenCalled();
    }
}
