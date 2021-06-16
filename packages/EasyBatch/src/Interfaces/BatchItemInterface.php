<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemInterface extends BatchObjectInterface
{
    /**
     * @var string
     */
    public const STATUS_BATCH_PENDING_APPROVAL = 'batch_pending_approval';

    public function getAttempts(): int;

    /**
     * @return int|string
     */
    public function getBatchId();

    public function getReason(): ?string;

    /**
     * @return null|mixed[]
     */
    public function getReasonParams(): ?array;

    public function getTargetClass(): string;

    public function isApprovalRequired(): bool;

    public function isRetried(): bool;

    public function setApprovalRequired(?bool $approvalRequired = null): self;

    public function setAttempts(int $attempts): self;

    /**
     * @param int|string $batchId
     */
    public function setBatchId($batchId): self;

    public function setReason(string $reason): self;

    /**
     * @param mixed[] $params
     */
    public function setReasonParams(array $params): self;

    public function setTargetClass(string $targetClass): self;
}
