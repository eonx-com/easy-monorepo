<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchFactoryInterface
{
    public function create(?callable $itemsProvider = null): BatchInterface;

    public function createFromCallable(callable $itemsProvider): BatchInterface;

    /**
     * @param iterable<object> $items
     */
    public function createFromIterable(iterable $items): BatchInterface;

    /**
     * @param object $item
     */
    public function createFromObject($item): BatchInterface;
}
