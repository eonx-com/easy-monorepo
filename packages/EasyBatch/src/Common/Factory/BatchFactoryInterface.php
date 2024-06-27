<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Factory;

use EonX\EasyBatch\Common\ValueObject\BatchInterface;

interface BatchFactoryInterface extends BatchObjectFactoryInterface
{
    public function createFromCallable(callable $itemsProvider, ?string $class = null): BatchInterface;

    /**
     * @param iterable<object> $items
     */
    public function createFromIterable(iterable $items, ?string $class = null): BatchInterface;

    public function createFromObject(object $item, ?string $class = null): BatchInterface;
}
