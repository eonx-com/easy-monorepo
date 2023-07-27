<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchInterface extends BatchObjectInterface
{
    public function countCancelled(): int;

    public function countFailed(): int;

    public function countProcessed(): int;

    public function countSucceeded(): int;

    public function countTotal(): int;

    /**
     * @return iterable<object>
     */
    public function getItems(): iterable;

    public function getParentBatchItemId(): int|string|null;

    public function setCancelled(int $cancelled): self;

    public function setFailed(int $failed): self;

    /**
     * @param iterable<object> $items
     */
    public function setItems(iterable $items): self;

    public function setItemsProvider(callable $itemsProvider): self;

    public function setParentBatchItemId(int|string $batchItemId): self;

    public function setProcessed(int $processed): self;

    public function setSucceeded(int $succeeded): self;

    public function setTotal(int $total): self;
}
