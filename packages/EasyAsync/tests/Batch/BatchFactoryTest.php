<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Batch;

use EonX\EasyAsync\Batch\BatchFactory;
use EonX\EasyAsync\Tests\AbstractBatchTestCase;
use Ramsey\Uuid\Uuid;

final class BatchFactoryTest extends AbstractBatchTestCase
{
    public function testCreate(): void
    {
        $batch = $this->getFactory()->create();

        self::assertTrue(Uuid::isValid($batch->getId()));
    }

    /**
     * @dataProvider providerTestFromCallable
     */
    public function testCreateFromCallable(callable $itemsProvider, int $count): void
    {
        $batch = $this->getFactory()->createFromCallable($itemsProvider);

        self::assertTrue(Uuid::isValid($batch->getId()));
        self::assertCount($count, $batch->getItems());
    }

    /**
     * @param iterable<object> $items
     *
     * @dataProvider providerTestFromIterable
     */
    public function testCreateFromIterable(iterable $items, int $count): void
    {
        $batch = $this->getFactory()->createFromIterable($items);

        self::assertTrue(Uuid::isValid($batch->getId()));
        self::assertCount($count, $batch->getItems());
    }

    public function testCreateFromObject(): void
    {
        $batch = $this->getFactory()->createFromObject(new \stdClass());

        self::assertTrue(Uuid::isValid($batch->getId()));
        self::assertCount(1, $batch->getItems());
    }

    private function getFactory(): BatchFactory
    {
        return new BatchFactory($this->getRandomGenerator());
    }
}
