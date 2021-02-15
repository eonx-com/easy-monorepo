<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class BatchFactory implements BatchFactoryInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    public function __construct(RandomGeneratorInterface $random)
    {
        $this->random = $random;
    }

    public function create(?callable $itemsProvider = null): BatchInterface
    {
        return $this->modifyBatch(new Batch($itemsProvider));
    }

    public function createFromCallable(callable $itemsProvider): BatchInterface
    {
        return $this->modifyBatch(Batch::fromCallable($itemsProvider));
    }

    /**
     * @param iterable<object> $items
     */
    public function createFromIterable(iterable $items): BatchInterface
    {
        return $this->modifyBatch(Batch::fromIterable($items));
    }

    /**
     * @param object $item
     */
    public function createFromObject($item): BatchInterface
    {
        return $this->modifyBatch(Batch::fromObject($item));
    }

    private function modifyBatch(BatchInterface $batch): BatchInterface
    {
        return $batch->setId($this->random->uuidV4());
    }
}
