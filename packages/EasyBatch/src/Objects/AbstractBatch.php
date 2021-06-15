<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchInterface;

abstract class AbstractBatch extends AbstractBatchObject implements BatchInterface
{
    /**
     * @var int|string
     */
    private $batchItemId;

    /**
     * @var int
     */
    private $failed = 0;

    /**
     * @var null|callable
     */
    private $itemsProvider;

    /**
     * @var null|string
     */
    private $name;

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
    public function getBatchItemId()
    {
        return $this->batchItemId;
    }

    /**
     * @return iterable<object>
     */
    public function getItems(): iterable
    {
        return $this->itemsProvider !== null ? \call_user_func($this->itemsProvider) : [];
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param int|string $batchItemId
     */
    public function setBatchItemId($batchItemId): BatchInterface
    {
        $this->batchItemId = $batchItemId;

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

    public function setName(?string $name = null): BatchInterface
    {
        $this->name = $name;

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
            'batch_item_id' => $this->getBatchItemId(),
            'failed' => $this->countFailed(),
            'name' => $this->getName(),
            'processed' => $this->countProcessed(),
            'succeeded' => $this->countSucceeded(),
            'total' => $this->countTotal(),
        ]);
    }
}
