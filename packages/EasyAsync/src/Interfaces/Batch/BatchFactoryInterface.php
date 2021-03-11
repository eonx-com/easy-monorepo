<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchFactoryInterface
{
    public function createFromCallable(callable $itemsProvider, ?string $class = null): BatchInterface;

    /**
     * @param iterable<object> $items
     */
    public function createFromIterable(iterable $items, ?string $class = null): BatchInterface;

    /**
     * @param object $item
     */
    public function createFromObject($item, ?string $class = null): BatchInterface;
}
