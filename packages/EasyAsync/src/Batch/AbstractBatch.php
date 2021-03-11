<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchInterface;

abstract class AbstractBatch implements BatchInterface
{
    /**
     * @var \DateTimeInterface
     */
    private $cancelledAt;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var int
     */
    private $failed = 0;

    /**
     * @var \DateTimeInterface
     */
    private $finishedAt;

    /**
     * @var string
     */
    private $id;

    /**
     * @var null|callable
     */
    private $itemsProvider;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $processed = 0;

    /**
     * @var \DateTimeInterface
     */
    private $startedAt;

    /**
     * @var string
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var int
     */
    private $succeeded = 0;

    /**
     * @var \Throwable
     */
    private $throwable;

    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var \DateTimeInterface
     */
    private $updatedAt;

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

    public function getCancelledAt(): ?\DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCancelledAt(\DateTimeInterface $cancelledAt): BatchInterface
    {
        $this->cancelledAt = $cancelledAt;

        return $this;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): BatchInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setFailed(int $failed): BatchInterface
    {
        $this->failed = $failed;

        return $this;
    }

    public function setFinishedAt(\DateTimeInterface $finishedAt): BatchInterface
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function setId(string $id): BatchInterface
    {
        $this->id = $id;

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

    public function setName(string $name): BatchInterface
    {
        $this->name = $name;

        return $this;
    }

    public function setProcessed(int $processed): BatchInterface
    {
        $this->processed = $processed;

        return $this;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): BatchInterface
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function setStatus(string $status): BatchInterface
    {
        $this->status = $status;

        return $this;
    }

    public function setSucceeded(int $succeeded): BatchInterface
    {
        $this->succeeded = $succeeded;

        return $this;
    }

    public function setThrowable(\Throwable $throwable): BatchInterface
    {
        $this->throwable = $throwable;

        return $this;
    }

    public function setTotal(int $total): BatchInterface
    {
        $this->total = $total;

        return $this;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): BatchInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'cancelled_at' => $this->getCancelledAt(),
            'created_at' => $this->getCreatedAt(),
            'id' => $this->getId(),
            'failed' => $this->countFailed(),
            'finished_at' => $this->getFinishedAt(),
            'name' => $this->getName(),
            'processed' => $this->countProcessed(),
            'succeeded' => $this->countSucceeded(),
            'total' => $this->countTotal(),
            'started_at' => $this->getStartedAt(),
            'status' => $this->getStatus(),
            'throwable' => $this->getThrowable(),
            'updated_at' => $this->getUpdatedAt(),
        ];
    }
}
