<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyEntityChange\Tests\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use LoyaltyCorp\EasyEntityChange\Doctrine\EntityChangeSubscriber;
use LoyaltyCorp\EasyEntityChange\Exceptions\InvalidDispatcherException;
use LoyaltyCorp\EasyEntityChange\Tests\AbstractTestCase;
use LoyaltyCorp\EasyEntityChange\Tests\Stubs\EventDispatcherStub;
use stdClass;

final class EntityChangeSubscriberTest extends AbstractTestCase
{
    /**
     * Test events subscription.
     *
     * @return void
     */
    public function testSubscribedEvents(): void
    {
        $dispatcherStub = new EventDispatcherStub();
        $subscriber = new EntityChangeSubscriber($dispatcherStub);

        static::assertSame(['onFlush', 'postFlush'], $subscriber->getSubscribedEvents());
    }

    /**
     * Test events subscription.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyEntityChange\Exceptions\InvalidDispatcherException
     */
    public function testThrowOnInvalidDispatch(): void
    {
        $dispatcherStub = new EventDispatcherStub();
        // Simulate a misconfigured dispatcher
        $dispatcherStub->addReturn(null);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $unitOfWork->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);
        $unitOfWork->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([new stdClass()]);
        $unitOfWork->expects(static::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);
        $unitOfWork->expects(static::once())
            ->method('getScheduledCollectionDeletions')
            ->willReturn([]);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);
        $eventArgs = new OnFlushEventArgs($entityManager);
        $subscriber = new EntityChangeSubscriber($dispatcherStub);
        $subscriber->onFlush($eventArgs);

        $this->expectException(InvalidDispatcherException::class);
        $this->expectExceptionMessage('exceptions.services.entitychange.doctrine.invalid_dispatcher');

        $subscriber->postFlush();
    }

    /**
     * Test events subscription.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyEntityChange\Exceptions\InvalidDispatcherException
     */
    public function testNoDispatch(): void
    {
        $dispatcherStub = new EventDispatcherStub();
        // Simulate a misconfigured dispatcher
        $dispatcherStub->addReturn(null);

        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects(static::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);
        $unitOfWork->expects(static::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);
        $unitOfWork->expects(static::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);
        $unitOfWork->expects(static::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);
        $unitOfWork->expects(static::once())
            ->method('getScheduledCollectionDeletions')
            ->willReturn([]);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects(static::once())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);
        $eventArgs = new OnFlushEventArgs($entityManager);
        $subscriber = new EntityChangeSubscriber($dispatcherStub);
        $subscriber->onFlush($eventArgs);

        $subscriber->postFlush();

        self::assertSame([], $dispatcherStub->getDispatched());
    }
}
