<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Factories;

use EonX\EasyBatch\Objects\BatchItem;
use EonX\EasyBatch\Events\BatchItemCreatedEvent;
use EonX\EasyBatch\Factories\BatchItemFactory;
use EonX\EasyBatch\Tests\AbstractTestCase;
use EonX\EasyBatch\Tests\Stubs\BatchItemStub;
use EonX\EasyEventDispatcher\Bridge\Symfony\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

final class BatchItemFactoryTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestCreateSuccess(): iterable
    {
        yield 'default class' => [null];

        yield 'custom class' => [BatchItemStub::class];
    }

    /**
     * @return iterable<mixed>
     */
    public function providerTestCreateFromArraySuccess(): iterable
    {
        yield 'default test' => [
            [
                'id' => 'id',
                'batch_id' => 'batch-id',
                'target_class' => 'target-class',
            ],
        ];
    }

    /**
     * @phpstan-param class-string $class
     *
     * @dataProvider providerTestCreateSuccess
     */
    public function testCreateSuccess(?string $class = null): void
    {
        $factory = new BatchItemFactory();

        $batchItem = $factory->create('batch-id', 'target-class', $class);

        self::assertInstanceOf($class === null ? BatchItem::class : $class, $batchItem);
        self::assertEquals('batch-id', $batchItem->getBatchId());
        self::assertEquals('target-class', $batchItem->getTargetClass());
    }

    public function testCreateCustomClassViaEvent(): void
    {
        $listener = static function (BatchItemCreatedEvent $event): void {
            $event->setBatchItem(new BatchItemStub());
        };

        $sfEventDispatcher = new SymfonyEventDispatcher();
        $sfEventDispatcher->addListener(BatchItemCreatedEvent::class, $listener);
        $eventDispatcher = new EventDispatcher($sfEventDispatcher);

        $factory = new BatchItemFactory(null, null, $eventDispatcher);

        $batchItem = $factory->create('batch-id', 'target-class');

        self::assertInstanceOf(BatchItemStub::class, $batchItem);
    }

    /**
     * @param mixed[] $data
     *
     * @dataProvider providerTestCreateFromArraySuccess
     */
    public function testCreateFromArraySuccess(array $data): void
    {
        $factory = new BatchItemFactory();

        $batchItem = $factory->createFromArray($data);

        self::assertInstanceOf($data['class'] ?? BatchItem::class, $batchItem);
    }
}
