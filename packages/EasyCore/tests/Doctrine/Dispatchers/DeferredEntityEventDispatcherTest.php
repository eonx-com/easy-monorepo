<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Doctrine\Dispatchers;

use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyCore\Doctrine\Events\EntityCreatedEvent;
use EonX\EasyCore\Doctrine\Events\EntityUpdatedEvent;
use EonX\EasyCore\Tests\AbstractTestCase;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;
use stdClass;

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
                'entityInsertions' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                ],
                'entityUpdates' => [
                    2 => [
                        'foo' => 'bar',
                    ],
                ],
                'expectedEntityInsertions' => [],
                'expectedEntityUpdates' => [],
                'transactionNestingLevel' => null,
            ],
            'transaction nesting level is not null' => [
                'entityInsertions' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                    2 => [
                        'key' => 'value',
                    ],
                    3 => [
                        'john' => 'doe',
                    ],
                ],
                'entityUpdates' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                    2 => [
                        'key' => 'value',
                    ],
                    3 => [
                        'john' => 'doe',
                    ],
                ],
                'expectedEntityInsertions' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                    2 => [],
                    3 => [],
                ],
                'expectedEntityUpdates' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                    2 => [],
                    3 => [],
                ],
                'transactionNestingLevel' => 2,
            ],
            'transaction nesting level does not exist' => [
                'entityInsertions' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                ],
                'entityUpdates' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                ],
                'expectedEntityInsertions' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                ],
                'expectedEntityUpdates' => [
                    1 => [
                        'foo' => 'bar',
                    ],
                ],
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
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);
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
        $entityA = $this->prophesize(stdClass::class)->reveal();
        $entityB = $this->prophesize(stdClass::class)->reveal();
        /** @var object[] $entityAB */
        $entityAB = [$entityA, $entityB];
        $entityC = $this->prophesize(stdClass::class)->reveal();
        $entityD = $this->prophesize(stdClass::class)->reveal();
        /** @var object[] $entityCD */
        $entityCD = [$entityC, $entityD];
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);

        $deferredEntityEventDispatcher->deferInsertions($entityAB, 0);
        $deferredEntityEventDispatcher->deferInsertions($entityCD, 0);

        self::assertSame(
            [
                0 => [$entityA, $entityB, $entityC, $entityD],
            ],
            $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions')
        );
    }

    public function testDeferInsertionsSucceedsWithDisabled(): void
    {
        $entityA = $this->prophesize(stdClass::class)->reveal();
        $entityB = $this->prophesize(stdClass::class)->reveal();
        /** @var object[] $entityAB */
        $entityAB = [$entityA, $entityB];
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);
        $deferredEntityEventDispatcher->disable();

        $deferredEntityEventDispatcher->deferInsertions($entityAB, 0);

        self::assertSame(
            [],
            $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions')
        );
    }

    public function testDeferUpdatesSucceeds(): void
    {
        $entityA = $this->prophesize(stdClass::class)->reveal();
        $entityB = $this->prophesize(stdClass::class)->reveal();
        /** @var object[] $entityAB */
        $entityAB = [$entityA, $entityB];
        $entityC = $this->prophesize(stdClass::class)->reveal();
        $entityD = $this->prophesize(stdClass::class)->reveal();
        /** @var object[] $entityCD */
        $entityCD = [$entityC, $entityD];
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);

        $deferredEntityEventDispatcher->deferUpdates($entityAB, 0);
        $deferredEntityEventDispatcher->deferUpdates($entityCD, 0);

        self::assertSame(
            [
                0 => [$entityA, $entityB, $entityC, $entityD],
            ],
            $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates')
        );
    }

    public function testDeferUpdatesSucceedsWidthDisabled(): void
    {
        $entityA = $this->prophesize(stdClass::class)->reveal();
        $entityB = $this->prophesize(stdClass::class)->reveal();
        /** @var object[] $entityAB */
        $entityAB = [$entityA, $entityB];
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);
        $deferredEntityEventDispatcher->disable();

        $deferredEntityEventDispatcher->deferUpdates($entityAB, 0);

        self::assertSame(
            [],
            $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates')
        );
    }

    public function testDisableSucceeds(): void
    {
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);

        $deferredEntityEventDispatcher->disable();

        self::assertFalse($this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'enabled'));
    }

    public function testDispatchSucceedsWithEntities(): void
    {
        /** @var object $newEntity */
        $newEntity = $this->prophesize(stdClass::class)->reveal();
        /** @var object $existedEntity */
        $existedEntity = $this->prophesize(stdClass::class)->reveal();
        $entityCreatedEvent = new EntityCreatedEvent($newEntity);
        $entityUpdatedEvent = new EntityUpdatedEvent($existedEntity);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch($entityCreatedEvent)
            ->willReturnArgument(0);
        $eventDispatcher->dispatch($entityUpdatedEvent)
            ->willReturnArgument(0);
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);

        $deferredEntityEventDispatcher->deferInsertions([$newEntity], 0);
        $deferredEntityEventDispatcher->deferUpdates([$existedEntity], 0);
        $deferredEntityEventDispatcher->dispatch();

        $eventDispatcher->dispatch($entityCreatedEvent)
            ->shouldHaveBeenCalledOnce();
        $eventDispatcher->dispatch($entityUpdatedEvent)
            ->shouldHaveBeenCalledOnce();
        self::assertSame([], $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions'));
        self::assertSame([], $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates'));
    }

    public function testDispatchSucceedsWithoutEntities(): void
    {
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $eventDispatcher->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);

        $deferredEntityEventDispatcher->dispatch();

        $eventDispatcher->dispatch(Argument::type(EntityCreatedEvent::class))
            ->shouldNotHaveBeenCalled();
        $eventDispatcher->dispatch(Argument::type(EntityUpdatedEvent::class))
            ->shouldNotHaveBeenCalled();
        self::assertSame([], $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityInsertions'));
        self::assertSame([], $this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'entityUpdates'));
    }

    public function testEnableSucceeds(): void
    {
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);
        $deferredEntityEventDispatcher->disable();

        $deferredEntityEventDispatcher->enable();

        self::assertTrue($this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'enabled'));
    }

    public function testEnabledSucceedsAndContainsTrueByDefault(): void
    {
        /** @var \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcherReveal */
        $eventDispatcherReveal = $this->prophesize(EventDispatcherInterface::class)->reveal();
        $deferredEntityEventDispatcher = new DeferredEntityEventDispatcher($eventDispatcherReveal);

        self::assertTrue($this->getPrivatePropertyValue($deferredEntityEventDispatcher, 'enabled'));
    }
}
