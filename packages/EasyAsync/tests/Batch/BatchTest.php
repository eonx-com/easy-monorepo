<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Batch;

use EonX\EasyAsync\Batch\Batch;
use EonX\EasyAsync\Tests\AbstractBatchTestCase;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
final class BatchTest extends AbstractBatchTestCase
{
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
