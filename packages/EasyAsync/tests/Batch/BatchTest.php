<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Batch;

use EonX\EasyAsync\Batch\Batch;
use EonX\EasyAsync\Tests\AbstractBatchTestCase;

final class BatchTest extends AbstractBatchTestCase
{
    /**
     * @dataProvider providerTestFromCallable
     */
    public function testFromCallable(callable $itemsProvider, int $count): void
    {
        $batch = Batch::fromCallable($itemsProvider);
        $batch->setId('my-id');

        $items = $batch->getItems();

        self::assertCount($count, $items);
        self::assertEquals('my-id', $batch->getId());
    }

    /**
     * @param iterable<mixed> $items
     *
     * @dataProvider providerTestFromIterable
     */
    public function testFromIterable(iterable $items, int $count): void
    {
        $batch = Batch::fromIterable($items);

        $items = $batch->getItems();

        self::assertCount($count, $items);
    }

    public function testFromObject(): void
    {
        $batch = Batch::fromObject(new \stdClass());

        $items = $batch->getItems();

        self::assertCount(1, $items);
    }

    public function testSetItems(): void
    {
        $batch = new Batch();
        $batch->setItems([new \stdClass()]);

        $items = $batch->getItems();

        self::assertCount(1, $items);
    }

    public function testSetItemsProvider(): void
    {
        $itemsProvider = static function (): iterable {
            yield new \stdClass();
        };

        $batch = new Batch();
        $batch->setItemsProvider($itemsProvider);

        self::assertCount(1, $batch->getItems());
    }
}
