<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Factory;

use EonX\EasyBatch\Common\ValueObject\Batch;

interface BatchFactoryInterface extends BatchObjectFactoryInterface
{
    public function createFromCallable(callable $itemsProvider, ?string $class = null): Batch;

    /**
     * @param iterable<object> $items
     */
    public function createFromIterable(iterable $items, ?string $class = null): Batch;

    public function createFromObject(object $item, ?string $class = null): Batch;
}
