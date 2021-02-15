<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchInterface
{
    /**
     * @var string
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @var string
     */
    public const STATUS_FAILED = 'failed';

    /**
     * @var string
     */
    public const STATUS_PENDING = 'pending';

    /**
     * @var string
     */
    public const STATUS_PROCESSING = 'processing';

    /**
     * @var string
     */
    public const STATUS_SUCCESS = 'success';

    public function countFailed(): int;

    public function countProcessed(): int;

    public function countSucceeded(): int;

    public function countTotal(): int;

    public function getId(): ?string;

    /**
     * @return iterable<object>
     */
    public function getItems(): iterable;

    public function getStatus(): string;

    public function getThrowable(): ?\Throwable;

    public function setFailed(int $failed): self;

    public function setId(string $id): self;

    /**
     * @param iterable<object> $items
     */
    public function setItems(iterable $items): self;

    public function setItemsProvider(callable $itemsProvider): self;

    public function setProcessed(int $processed): self;

    public function setStatus(string $status): self;

    public function setSucceeded(int $succeeded): self;

    public function setThrowable(\Throwable $throwable): self;

    public function setTotal(int $total): self;
}
