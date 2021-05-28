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

    public function getCancelledAt(): ?\DateTimeInterface;

    public function getCreatedAt(): \DateTimeInterface;

    public function getFinishedAt(): ?\DateTimeInterface;

    public function getId(): ?string;

    /**
     * @return iterable<object>
     */
    public function getItems(): iterable;

    public function getName(): ?string;

    public function getStartedAt(): ?\DateTimeInterface;

    public function getStatus(): string;

    public function getThrowable(): ?\Throwable;

    public function getUpdatedAt(): \DateTimeInterface;

    public function isCompleted(): bool;

    public function setCancelledAt(\DateTimeInterface $cancelledAt): self;

    public function setCreatedAt(\DateTimeInterface $createdAt): self;

    public function setFailed(int $failed): self;

    public function setFinishedAt(\DateTimeInterface $finishedAt): self;

    public function setId(string $id): self;

    /**
     * @param iterable<object> $items
     */
    public function setItems(iterable $items): self;

    public function setItemsProvider(callable $itemsProvider): self;

    public function setName(?string $name = null): self;

    public function setProcessed(int $processed): self;

    public function setStartedAt(\DateTimeInterface $startedAt): self;

    public function setStatus(string $status): self;

    public function setSucceeded(int $succeeded): self;

    public function setThrowable(\Throwable $throwable): self;

    public function setTotal(int $total): self;

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self;

    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
