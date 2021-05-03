<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Dispatchers;

use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyCore\Doctrine\Events\EntityCreatedEvent;
use EonX\EasyCore\Doctrine\Events\EntityUpdatedEvent;
use EonX\EasyCore\Interfaces\DatabaseEntityInterface;
use EonX\EasyCore\Tests\AbstractTestCase;
use Prophecy\Argument;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcher
 */
final class DeferredEntityEventDispatcherTest extends AbstractTestCase
{
    /**
     * @return mixed[]
     *
     * @see testClearSucceeds
     */
    public function provideDataForClear(): array
    {
        return [
            'transaction nesting level is null' => [
                'entityInsertions' => [1 => ['foo' => 'bar']],
                'entityUpdates' => [2 => ['foo' => 'bar']],
                'expectedEntityInsertions' => [],
                'expectedEntityUpdates' => [],
                'transactionNestingLevel' => null,
            ],
            'transaction nesting level is not null' => [
                'entityInsertions' => [1 => ['foo' => 'bar'], 2 => ['key' => 'value'], 3 => ['john' => 'doe']],
                'entityUpdates' => [1 => ['foo' => 'bar'], 2 => ['key' => 'value'], 3 => ['john' => 'doe']],
                'expectedEntityInsertions' => [1 => ['foo' => 'bar'], 2 => [], 3 => []],
                'expectedEntityUpdates' => [1 => ['foo' => 'bar'], 2 => [], 3 => []],
                'transactionNestingLevel' => 2,
            ],
            'transaction nesting level does not exist' => [
                'entityInsertions' => [1 => ['foo' => 'bar']],
                'entityUpdates' => [1 => ['foo' => 'bar']],
                'expectedEntityInsertions' => [1 => ['foo' => 'bar']],
                'expectedEntityUpdates' => [1 => ['foo' => 'bar']],
                'transactionNestingLevel' => 3,
            ],
        ];
    }

    /**
     * @param mixed[] $entityInsertions
     * @param mixed[] $entityUpdates
     * @param mixed[] $expectedEntityInsertions
     * @param mixed[] $expectedEntityUpdates
     *
     * @dataProvider provideDataForClear
     */
    public function testClearSucceeds(
        array $entityInsertions,
        array $entityUpdates,
        array $expectedEntityInsertions,
        array $expectedEntityUpdates,
        ?int $transactionNestingLevel = null
    ): void {
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher(
            $this->prophesize(EventDispatcherInterface::class)->reveal()
        );
        $this->setPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions', $entityInsertions);
        $this->setPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates', $entityUpdates);

        $deferredEntityEventDispatcher->clear($transactionNestingLevel);

        self::assertSame(
            $expectedEntityInsertions,
            $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions')
        );
        self::assertSame(
            $expectedEntityUpdates,
            $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates')
        );
    }

    public function testDeferInsertionsSucceeds(): void
    {
        $entityA = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $entityB = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $entityC = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $entityD = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher(
            $this->prophesize(EventDispatcherInterface::class)->reveal()
        );

        $deferredEntityEventDispatcher->deferInsertions(['key-1' => $entityA, 'key-2' => $entityB], 0);
        $deferredEntityEventDispatcher->deferInsertions(['key-1' => $entityD, 'key-3' => $entityC], 0);

        self::assertSame(
            [0 => ['key-1' => $entityD, 'key-2' => $entityB, 'key-3' => $entityC]],
            $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions')
        );
    }

    public function testDeferUpdatesSucceeds(): void
    {
        $entityA = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $entityB = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $entityC = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $entityD = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher(
            $this->prophesize(EventDispatcherInterface::class)->reveal()
        );

        $deferredEntityEventDispatcher->deferUpdates(['key-1' => $entityA, 'key-2' => $entityB], 0);
        $deferredEntityEventDispatcher->deferUpdates(['key-1' => $entityD, 'key-3' => $entityC], 0);

        self::assertSame(
            [0 => ['key-1' => $entityD, 'key-2' => $entityB, 'key-3' => $entityC]],
            $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates')
        );
    }

    public function testDispatchSucceedsWithEntities(): void
    {
        $newEntity = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $existedEntity = $this->prophesize(DatabaseEntityInterface::class)->reveal();
        $entityCreatedEvent = new EntityCreatedEvent($newEntity);
        $entityUpdatedEvent = new EntityUpdatedEvent($existedEntity);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch($entityCreatedEvent, EntityCreatedEvent::NAME)->willReturnArgument(0);
        $eventDispatcher->dispatch($entityUpdatedEvent, EntityUpdatedEvent::NAME)->willReturnArgument(0);
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcher->reveal());

        $deferredEntityEventDispatcher->deferInsertions([$newEntity], 0);
        $deferredEntityEventDispatcher->deferUpdates([$existedEntity], 0);
        $deferredEntityEventDispatcher->dispatch();

        $eventDispatcher->dispatch($entityCreatedEvent, EntityCreatedEvent::NAME)->shouldHaveBeenCalledOnce();
        $eventDispatcher->dispatch($entityUpdatedEvent, EntityUpdatedEvent::NAME)->shouldHaveBeenCalledOnce();
        self::assertSame([], $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions'));
        self::assertSame([], $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates'));
    }

    public function testDispatchSucceedsWithoutEntities(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcher->reveal());

        $deferredEntityEventDispatcher->dispatch();

        $eventDispatcher->dispatch(Argument::type(EntityCreatedEvent::class), EntityCreatedEvent::NAME)
            ->shouldNotHaveBeenCalled();
        $eventDispatcher->dispatch(Argument::type(EntityUpdatedEvent::class), EntityUpdatedEvent::NAME)
            ->shouldNotHaveBeenCalled();
        self::assertSame([], $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions'));
        self::assertSame([], $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates'));
    }
}
