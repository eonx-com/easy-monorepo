<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;

abstract class AbstractBatchFactoryDecorator implements BatchFactoryInterface
{
    /**
     * @var \EonX\EasyAsync\Interfaces\Batch\BatchFactoryInterface
     */
    protected $decorated;

    public function __construct(BatchFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function createFromCallable(callable $itemsProvider, ?string $class = null): BatchInterface
    {
        return $this->decorated->createFromCallable($itemsProvider, $class);
    }

    /**
     * @param iterable<object> $items
     */
    public function createFromIterable(iterable $items, ?string $class = null): BatchInterface
    {
        return $this->decorated->createFromIterable($items, $class);
    }

    /**
     * @param object $item
     */
    public function createFromObject($item, ?string $class = null): BatchInterface
    {
        return $this->decorated->createFromObject($item, $class);
    }
}
