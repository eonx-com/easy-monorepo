<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Subscribers;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyDoctrine\Tests\AbstractTestCase;
use stdClass;

/**
 * @covers \EonX\EasyDoctrine\Subscribers\EntityEventSubscriber
 */
final class EntityEventSubscriberTest extends AbstractTestCase
{
    public function testGetSubscribedEventsSucceeds(): void
    {
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->prophesize(DeferredEntityEventDispatcherInterface::class)->reveal();
        $entityEventSubscriber = new EntityEventSubscriber($eventDispatcher, []);

        $subscribedEvents = $entityEventSubscriber->getSubscribedEvents();

        self::assertSame([Events::onFlush, Events::postFlush], $subscribedEvents);
    }

    public function testOnFlushSucceeds(): void
    {
        $newEntity = $this->prophesize(stdClass::class)->reveal();
        $existedEntity = $this->prophesize(stdClass::class)->reveal();
        $notAcceptableEntity = $this->prophesize(stdClass::class)
            ->willImplement(EntityManagerInterface::class)
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
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredEntityEventDispatcherReveal */
        $deferredEntityEventDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $entityEventSubscriber = new EntityEventSubscriber(
            $deferredEntityEventDispatcherReveal,
            [\get_class($newEntity), \get_class($existedEntity)]
        );
        /** @var \Doctrine\ORM\Event\OnFlushEventArgs $eventArgsReveal */
        $eventArgsReveal = $eventArgs->reveal();

        $entityEventSubscriber->onFlush($eventArgsReveal);

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
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredEntityEventDispatcherReveal */
        $deferredEntityEventDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $entityEventSubscriber = new EntityEventSubscriber($deferredEntityEventDispatcherReveal, []);
        /** @var \Doctrine\ORM\Event\PostFlushEventArgs $eventArgsReveal */
        $eventArgsReveal = $eventArgs->reveal();

        $entityEventSubscriber->postFlush($eventArgsReveal);

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
        /** @var \EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface $deferredEntityEventDispatcherReveal */
        $deferredEntityEventDispatcherReveal = $deferredEntityEventDispatcher->reveal();
        $entityEventSubscriber = new EntityEventSubscriber($deferredEntityEventDispatcherReveal, []);
        /** @var \Doctrine\ORM\Event\PostFlushEventArgs $eventArgsReveal */
        $eventArgsReveal = $eventArgs->reveal();

        $entityEventSubscriber->postFlush($eventArgsReveal);

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
