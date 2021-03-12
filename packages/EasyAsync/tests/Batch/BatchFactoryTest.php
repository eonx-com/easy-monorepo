<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Batch;

use EonX\EasyAsync\Tests\AbstractBatchTestCase;
use Ramsey\Uuid\Uuid;

final class BatchFactoryTest extends AbstractBatchTestCase
{
    /**
     * @dataProvider providerTestFromCallable
     */
    public function testCreateFromCallable(callable $itemsProvider, int $count): void
    {
        $batch = $this->getBatchFactory()->createFromCallable($itemsProvider);

        self::assertTrue(Uuid::isValid((string)$batch->getId()));
        self::assertCount($count, $batch->getItems());
    }

    /**
     * @param iterable<object> $items
     *
     * @dataProvider providerTestFromIterable
     */
    public function testCreateFromIterable(iterable $items, int $count): void
    {
        $batch = $this->getBatchFactory()->createFromIterable($items);

        self::assertTrue(Uuid::isValid((string)$batch->getId()));
        self::assertCount($count, $batch->getItems());
    }

    public function testCreateFromObject(): void
    {
        $batch = $this->getBatchFactory()->createFromObject(new \stdClass());

        self::assertTrue(Uuid::isValid((string)$batch->getId()));
        self::assertCount(1, $batch->getItems());
    }
}
