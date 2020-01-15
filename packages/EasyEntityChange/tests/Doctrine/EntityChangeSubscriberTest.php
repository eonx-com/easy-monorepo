<?php
declare(strict_types=1);

namespace EonX\EasyEntityChange\Tests\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use EonX\EasyEntityChange\DataTransferObjects\DeletedEntity;
use EonX\EasyEntityChange\DataTransferObjects\UpdatedEntity;
use EonX\EasyEntityChange\Doctrine\EntityChangeSubscriber;
use EonX\EasyEntityChange\Events\EntityChangeEvent;
use EonX\EasyEntityChange\Tests\AbstractTestCase;
use EonX\EasyEntityChange\Tests\Stubs\DeletedEntityEnrichmentStub;
use EonX\EasyEntityChange\Tests\Stubs\EventDispatcherStub;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @covers \EonX\EasyEntityChange\Doctrine\EntityChangeSubscriber
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityChangeSubscriberTest extends AbstractTestCase
{
    /**
     * Tests that the listener dispatches for updates.
     *
     * @return void
     */
    public function testListener(): void
    {
        $dispatcher = new EventDispatcherStub();
        $subscriber = new EntityChangeSubscriber($dispatcher);

        $entity = new stdClass();

        $unitOfWork = $this->getUnitOfWork(null, [$entity]);
        $unitOfWork->expects(self::once())
            ->method('getEntityChangeSet')
            ->with($entity)
            ->willReturn([
                'property' => ['blue', 'red']
            ]);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('getName')
            ->willReturn(stdClass::class);
        $metadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($entity)
            ->willReturn(['id' => 'thing']);

        $expectedEvent = new EntityChangeEvent([
            new UpdatedEntity(
                ['property'],
                stdClass::class,
                ['id' => 'thing']
            )
        ]);

        $this->callSubscriber($subscriber, $metadata, $unitOfWork);

        self::assertEquals([$expectedEvent], $dispatcher->getDispatched());
    }

    /**
     * Tests that the listener dispatches for inserts.
     *
     * @return void
     */
    public function testListenerCollection(): void
    {
        $dispatcher = new EventDispatcherStub();
        $subscriber = new EntityChangeSubscriber($dispatcher);

        $entity = new stdClass();

        $unitOfWork = $this->getUnitOfWork(null, null, null, [new ArrayCollection([
            $entity
        ])]);
        $unitOfWork->expects(self::once())
            ->method('getEntityChangeSet')
            ->with($entity)
            ->willReturn([
                'property' => ['green', 'purple']
            ]);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('getName')
            ->willReturn(stdClass::class);
        $metadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($entity)
            ->willReturn(['id' => 'seventy']);

        $expectedEvent = new EntityChangeEvent([
            new UpdatedEntity(
                ['property'],
                stdClass::class,
                ['id' => 'seventy']
            )
        ]);

        $this->callSubscriber($subscriber, $metadata, $unitOfWork);

        self::assertEquals([$expectedEvent], $dispatcher->getDispatched());
    }

    /**
     * Tests that the listener dispatches for deletes.
     *
     * @return void
     */
    public function testListenerDelete(): void
    {
        $dispatcher = new EventDispatcherStub();
        $enrichment = new DeletedEntityEnrichmentStub([['metadata' => 'thing']]);
        $subscriber = new EntityChangeSubscriber($dispatcher, $enrichment);

        $entity = new stdClass();

        $unitOfWork = $this->getUnitOfWork(null, null, [$entity]);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('getName')
            ->willReturn(stdClass::class);
        $metadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($entity)
            ->willReturn(['id' => 'value']);

        $expectedEvent = new EntityChangeEvent([
            new DeletedEntity(
                stdClass::class,
                ['id' => 'value'],
                ['metadata' => 'thing']
            )
        ]);

        $this->callSubscriber($subscriber, $metadata, $unitOfWork);

        self::assertEquals([$expectedEvent], $dispatcher->getDispatched());
    }

    /**
     * Tests that the listener does not dispatch if we're not watching
     * for the change.
     *
     * @return void
     */
    public function testListenerDeleteNotWatching(): void
    {
        $dispatcher = new EventDispatcherStub();
        $enrichment = new DeletedEntityEnrichmentStub([['metadata' => 'thing']]);
        $subscriber = new EntityChangeSubscriber($dispatcher, $enrichment, []);

        $entity = new stdClass();

        $unitOfWork = $this->getUnitOfWork(null, null, [$entity]);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('getName')
            ->willReturn(stdClass::class);
        $metadata->expects(self::never())
            ->method('getIdentifierValues');

        $this->callSubscriber($subscriber, $metadata, $unitOfWork);

        self::assertEquals([], $dispatcher->getDispatched());
    }

    /**
     * Tests that the listener does not dispatch if there are no
     * changes.
     *
     * @return void
     */
    public function testListenerDoesntDispatchOnEmptyChanges(): void
    {
        $dispatcher = new EventDispatcherStub();
        $subscriber = new EntityChangeSubscriber($dispatcher);

        $this->callSubscriber($subscriber);

        self::assertSame([], $dispatcher->getDispatched());
    }

    /**
     * Tests that the listener dispatches for inserts.
     *
     * @return void
     */
    public function testListenerInsert(): void
    {
        $dispatcher = new EventDispatcherStub();
        $subscriber = new EntityChangeSubscriber($dispatcher);

        $entity = new stdClass();

        $unitOfWork = $this->getUnitOfWork([$entity]);
        $unitOfWork->expects(self::once())
            ->method('getEntityChangeSet')
            ->with($entity)
            ->willReturn([
                'property' => ['old', 'new']
            ]);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('getName')
            ->willReturn(stdClass::class);
        $metadata->expects(self::exactly(2))
            ->method('getIdentifierValues')
            ->with($entity)
            ->willReturnOnConsecutiveCalls(
                [],
                ['id' => 'value']
            );

        $expectedEvent = new EntityChangeEvent([
            new UpdatedEntity(
                ['property'],
                stdClass::class,
                ['id' => 'value']
            )
        ]);

        $this->callSubscriber($subscriber, $metadata, $unitOfWork);

        self::assertEquals([$expectedEvent], $dispatcher->getDispatched());
    }

    /**
     * Tests that the listener does not dispatch if we're not watching
     * for the change.
     *
     * @return void
     */
    public function testListenerNotWatching(): void
    {
        $dispatcher = new EventDispatcherStub();
        $subscriber = new EntityChangeSubscriber($dispatcher, null, []);

        $entity = new stdClass();

        $unitOfWork = $this->getUnitOfWork(null, [$entity]);
        $unitOfWork->expects(self::never())
            ->method('getEntityChangeSet');

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('getName')
            ->willReturn(stdClass::class);
        $metadata->expects(self::never())
            ->method('getIdentifierValues');

        $this->callSubscriber($subscriber, $metadata, $unitOfWork);

        self::assertEquals([], $dispatcher->getDispatched());
    }

    /**
     * Tests subscribed events.
     *
     * @return void
     */
    public function testSubscribedEvents(): void
    {
        $subscriber = new EntityChangeSubscriber(new EventDispatcherStub());

        $events = $subscriber->getSubscribedEvents();

        self::assertSame([Events::onFlush, Events::postFlush], $events);
    }

    /**
     * Builds the Unit Of Work mock.
     *
     * @param mixed[]|null $insertions
     * @param mixed[]|null $updates
     * @param mixed[]|null $deletions
     * @param mixed[]|null $collectionUpdates
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     *
     * @phpstan-return \PHPUnit\Framework\MockObject\MockObject&\Doctrine\ORM\UnitOfWork
     */
    protected function getUnitOfWork(
        ?array $insertions = null,
        ?array $updates = null,
        ?array $deletions = null,
        ?array $collectionUpdates = null
    ): MockObject {
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($insertions ?? []);
        $unitOfWork->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($updates ?? []);
        $unitOfWork->expects(self::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn($deletions ?? []);
        $unitOfWork->expects(self::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn($collectionUpdates ?? []);

        return $unitOfWork;
    }

    /**
     * Calls the subscriber.
     *
     * @param \EonX\EasyEntityChange\Doctrine\EntityChangeSubscriber $subscriber
     * @param \Doctrine\ORM\Mapping\ClassMetadata|null $metadata
     * @param \Doctrine\ORM\UnitOfWork|null $unitOfWork
     *
     * @return void
     */
    private function callSubscriber(
        EntityChangeSubscriber $subscriber,
        ?ClassMetadata $metadata = null,
        ?UnitOfWork $unitOfWork = null
    ): void {
        if ($unitOfWork === null) {
            $unitOfWork = $this->createMock(UnitOfWork::class);
            $unitOfWork->expects(self::once())
                ->method('getScheduledEntityInsertions')
                ->willReturn([]);
            $unitOfWork->expects(self::once())
                ->method('getScheduledEntityUpdates')
                ->willReturn([]);
            $unitOfWork->expects(self::once())
                ->method('getScheduledEntityDeletions')
                ->willReturn([]);
            $unitOfWork->expects(self::once())
                ->method('getScheduledCollectionUpdates')
                ->willReturn([]);
        }

        if ($metadata === null) {
            $metadata = $this->createMock(ClassMetadata::class);
            $metadata->method('getName')
                ->willReturn(stdClass::class);
            $metadata->method('getIdentifierValues')
                ->willReturn(['id' => 'value']);
        }

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getUnitOfWork')
            ->willReturn($unitOfWork);
        $entityManager->method('getClassMetadata')
            ->with(stdClass::class)
            ->willReturn($metadata);

        $args = new OnFlushEventArgs($entityManager);

        $subscriber->onFlush($args);
        $subscriber->postFlush(new PostFlushEventArgs($entityManager));
    }
}
