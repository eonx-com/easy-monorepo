<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
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

    /**
     * @param mixed[] $data
     */
    public function instantiateFromArray(array $data): BatchInterface;
}
