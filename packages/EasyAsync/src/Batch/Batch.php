<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchInterface;

final class Batch implements BatchInterface
{
    /**
     * @var int
     */
    private $failed;

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
     * @var int
     */
    private $processed;

    /**
     * @var \DateTimeInterface
     */
    private $startedAt;

    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $succeeded;

    /**
     * @var \Throwable
     */
    private $throwable;

    /**
     * @var int
     */
    private $total;

    public function __construct(?callable $itemsProvider = null)
    {
        $this->itemsProvider = $itemsProvider;
        $this->status = self::STATUS_PENDING;
        $this->failed = 0;
        $this->processed = 0;
        $this->succeeded = 0;
        $this->total = 0;
    }

    public static function fromCallable(callable $itemsProvider): self
    {
        return new self($itemsProvider);
    }

    /**
     * @param iterable<object> $items
     */
    public static function fromIterable(iterable $items): self
    {
        return new self(self::getIterableCallable($items));
    }

    /**
     * @param object $item
     */
    public static function fromObject($item): self
    {
        return new self(self::getIterableCallable($item));
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
        $this->itemsProvider = self::getIterableCallable($items);

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

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'failed' => $this->countFailed(),
            'finished_at' => $this->getFinishedAt(),
            'processed' => $this->countProcessed(),
            'succeeded' => $this->countSucceeded(),
            'total' => $this->countTotal(),
            'started_at' => $this->getStartedAt(),
            'status' => $this->getStatus(),
            'throwable' => $this->getThrowable(),
        ];
    }

    /**
     * @param mixed $items
     */
    private static function getIterableCallable($items): callable
    {
        return static function () use ($items): iterable {
            return \is_iterable($items) ? $items : [$items];
        };
    }
}
