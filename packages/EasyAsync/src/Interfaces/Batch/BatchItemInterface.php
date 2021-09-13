<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
interface BatchItemInterface
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
     * @return string
     */
    public const STATUS_PENDING = 'pending';

    /**
     * @var string
     */
    public const STATUS_SUCCESS = 'success';

    /**
     * @var string
     */
    public const STATUS_SUCCESS_PENDING_APPROVAL = 'success_pending_approval';

    public function getAttempts(): int;

    public function getBatchId(): string;

    public function getFinishedAt(): ?\DateTimeInterface;

    public function getId(): string;

    public function getReason(): ?string;

    /**
     * @return null|mixed[]
     */
    public function getReasonParams(): ?array;

    public function getStartedAt(): ?\DateTimeInterface;

    public function getStatus(): string;

    public function getTargetClass(): string;

    public function getThrowable(): ?\Throwable;

    public function isApprovalRequired(): bool;

    public function isRetried(): bool;

    public function setApprovalRequired(?bool $approvalRequired = null): self;

    public function setAttempts(int $attempts): self;

    public function setFinishedAt(\DateTimeInterface $finishedAt): self;

    public function setId(string $id): self;

    public function setReason(string $reason): self;

    /**
     * @param mixed[] $params
     */
    public function setReasonParams(array $params): self;

    public function setStartedAt(\DateTimeInterface $startedAt): self;

    public function setStatus(string $status): self;

    public function setThrowable(\Throwable $throwable): self;

    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
