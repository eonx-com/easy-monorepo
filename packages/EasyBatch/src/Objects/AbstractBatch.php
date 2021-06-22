<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchInterface;

abstract class AbstractBatch extends AbstractBatchObject implements BatchInterface
{
    /**
     * @var int|string
     */
    private $parentBatchItemId;

    /**
     * @var int
     */
    private $failed = 0;

    /**
     * @var null|callable
     */
    private $itemsProvider;

    /**
     * @var int
     */
    private $processed = 0;

    /**
     * @var int
     */
    private $succeeded = 0;

    /**
     * @var int
     */
    private $total = 0;

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
     * @return null|int|string
     */
    public function getParentBatchItemId()
    {
        return $this->parentBatchItemId;
    }

    /**
     * @return iterable<object>
     */
    public function getItems(): iterable
    {
        return $this->itemsProvider !== null ? \call_user_func($this->itemsProvider) : [];
    }

    /**
     * @param int|string $batchItemId
     */
    public function setParentBatchItemId($batchItemId): BatchInterface
    {
        $this->parentBatchItemId = $batchItemId;

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
        $this->itemsProvider = static function () use ($items): iterable {
            return $items;
        };

        return $this;
    }

    public function setItemsProvider(callable $itemsProvider): BatchInterface
    {
        $this->itemsProvider = $itemsProvider;

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

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return \array_merge(parent::toArray(), [
            'failed' => $this->countFailed(),
            'parent_batch_item_id' => $this->getParentBatchItemId(),
            'processed' => $this->countProcessed(),
            'succeeded' => $this->countSucceeded(),
            'total' => $this->countTotal(),
        ]);
    }
}
