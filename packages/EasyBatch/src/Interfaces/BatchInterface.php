<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchInterface extends BatchObjectInterface
{
    public function countFailed(): int;

    public function countProcessed(): int;

    public function countSucceeded(): int;

    public function countTotal(): int;

    /**
     * @return null|int|string
     */
    public function getParentBatchItemId();

    /**
     * @return iterable<object>
     */
    public function getItems(): iterable;

    /**
     * @param int|string $batchItemId
     */
    public function setParentBatchItemId($batchItemId): self;

    public function setFailed(int $failed): self;

    /**
     * @param iterable<object> $items
     */
    public function setItems(iterable $items): self;

    public function setItemsProvider(callable $itemsProvider): self;

    public function setProcessed(int $processed): self;

    public function setSucceeded(int $succeeded): self;

    public function setTotal(int $total): self;
}
