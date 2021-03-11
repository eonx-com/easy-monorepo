<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class BatchFactory implements BatchFactoryInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    public function __construct(RandomGeneratorInterface $random, ?string $class = null)
    {
        $this->random = $random;
        $this->class = $class ?? Batch::class;
    }

    public function createFromCallable(callable $itemsProvider, ?string $class = null): BatchInterface
    {
        $batch = $this
            ->instantiateBatch($class)
            ->setItemsProvider($itemsProvider);

        return $this->modifyBatch($batch);
    }

    /**
     * @param iterable<object> $items
     */
    public function createFromIterable(iterable $items, ?string $class = null): BatchInterface
    {
        $batch = $this
            ->instantiateBatch($class)
            ->setItems($items);

        return $this->modifyBatch($batch);
    }

    /**
     * @param object $item
     */
    public function createFromObject($item, ?string $class = null): BatchInterface
    {
        return $this->createFromIterable([$item], $class);
    }

    private function instantiateBatch(?string $class = null): BatchInterface
    {
        $class = $class ?? $this->class;

        return new $class();
    }

    private function modifyBatch(BatchInterface $batch): BatchInterface
    {
        return $batch->setId($this->random->uuidV4());
    }
}
