<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use Closure;
use EonX\EasyBatch\Interfaces\BatchInterface;

abstract class AbstractBatch extends AbstractBatchObject implements BatchInterface
{
    private int $cancelled = 0;

    private int $failed = 0;

    private Closure|null $itemsProvider = null;

    private int|string|null $parentBatchItemId = null;

    private int $processed = 0;

    private int $succeeded = 0;

    private int $total = 0;

    public function countCancelled(): int
    {
        return $this->cancelled;
    }

    public function countFailed(): int
    {
        return $this->failed;
    }

    public function countProcessed(): int
    {
        return $this->processed;
    }

    public function countSucceeded(): int
    {
        return $this->succeeded;
    }

    public function countTotal(): int
    {
        return $this->total;
    }

    /**
     * @return iterable<object>
     */
    public function getItems(): iterable
    {
        return $this->itemsProvider !== null ? \call_user_func($this->itemsProvider) : [];
    }

    public function getParentBatchItemId(): int|string|null
    {
        return $this->parentBatchItemId;
    }

    public function setCancelled(int $cancelled): BatchInterface
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    public function setFailed(int $failed): BatchInterface
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * @param iterable<object> $items
     */
    public function setItems(iterable $items): BatchInterface
    {
        $this->itemsProvider = static fn (): iterable => $items;

        return $this;
    }

    public function setItemsProvider(callable $itemsProvider): BatchInterface
    {
        $this->itemsProvider = $itemsProvider(...);

        return $this;
    }

    public function setParentBatchItemId(int|string $batchItemId): BatchInterface
    {
        $this->parentBatchItemId = $batchItemId;

        return $this;
    }

    public function setProcessed(int $processed): BatchInterface
    {
        $this->processed = $processed;

        return $this;
    }

    public function setSucceeded(int $succeeded): BatchInterface
    {
        $this->succeeded = $succeeded;

        return $this;
    }

    public function setTotal(int $total): BatchInterface
    {
        $this->total = $total;

        return $this;
    }

    public function toArray(): array
    {
        return \array_merge(parent::toArray(), [
            'cancelled' => $this->countCancelled(),
            'failed' => $this->countFailed(),
            'parent_batch_item_id' => $this->getParentBatchItemId(),
            'processed' => $this->countProcessed(),
            'succeeded' => $this->countSucceeded(),
            'total' => $this->countTotal(),
        ]);
    }
}
